<?php

namespace Alexanevsky\DataResolver\Result;

use Alexanevsky\DataResolver\Exception\InvalidOptionException;
use Alexanevsky\DataResolver\Option\Option;
use Alexanevsky\DataResolver\Resolver;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use function Symfony\Component\String\u;

class Result implements \ArrayAccess, \Countable
{
    /**
     * The resolver in witch this result was called.
     */
    protected Resolver $resolver;

    /**
     * The resolved data.
     */
    protected array $data = [];

    /**
     * The resolved errors.
     */
    protected array $errors = [];

    public function __construct(Resolver $resolver, array $data)
    {
        $this->resolver = $resolver;
        $this->data = $this->resolver->getDefaults();

        foreach (array_keys($this->data) as $name) {
            if (!$this->resolver->has($name)) {
                continue;
            }

            $nameCamel = u($name)->camel()->toString();
            $nameSnake = u($name)->snake()->toString();

            if (in_array($name, array_keys($data))) {
                $dataName = $name;
            } else if (in_array($nameCamel, array_keys($data))) {
                $dataName = $nameCamel;
            } else if (in_array($nameSnake, array_keys($data))) {
                $dataName = $nameSnake;
            } else {
                continue;
            }

            $value = $data[$dataName];
            $option = $this->resolver->get($name);

            if ($option->getType() instanceof Resolver) {
                $value = $option->getType()->resolve($value);
            } elseif ($this->resolver->isAllowedTypeConversion() && $option->hasType()) {
                $value = $this->convertValue($value, $option->getType(), $option->isNullable());
            }

            if ($option->hasNormalizer()) {
                $value = $option->getNormalizer()($value, $option->getDefault(), $this->data);
            }

            $this->data[$name] = $value;
        }

        if ($this->resolver->hasNormalizer()) {
            $this->data = $this->resolver->getNormalizer()($this->data, $this->resolver->getDefaults());
        }

        $this->validate();
    }

    /**
     * Gets the resolved data.
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Gets the resolved data item.
     */
    public function get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Gets all resolved data.
     */
    public function toArray(): array
    {
        return array_map(function ($item) {
            return $item instanceof Result ? $item->toArray() : $item;
        }, $this->data);
    }

    /**
     * Gets count of data items.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Checks if data is valid.
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function hasDifferences(): bool
    {
        return $this->data !== $this->resolver->getDefaults();
    }

    public function hasData(): bool
    {
        return !empty(array_filter($this->data));
    }

    /**
     * Gets the errors indexed by options names.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Gets the first error message per each option.
     *
     * @return TranslatableMessage[]
     */
    public function getFirstErrors(): array
    {
        return array_map(function (array $errors): TranslatableMessage {
            return $errors[0];
        }, $this->errors);
    }

    /**
     * Gets the resolver in witch this result was called.
     */
    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetExists($name): bool
    {
        return isset($this->data[$name]);
    }

    public function offsetSet($name, $value): void
    {
        throw new \Exception('Setting options via array access is not supported.');
    }

    public function offsetUnset($name): void
    {
        throw new \Exception('Removing options via array access is not supported.');
    }

    /**
     * Validates the given data.
     */
    private function validate(): void
    {
        $validator = Validation::createValidator();
        $errors = [];

        foreach ($this->data as $name => $value) {
            $option = $this->resolver->get($name);
            $constraints = $option->getConstraints();
            $customValidators = $option->getValidators();

            if ($option->hasType() && !$option->getType() instanceof Resolver && !$this->verifyType($value, $option->getType(), $option->isNullable())) {
                throw new InvalidOptionException(sprintf('The option "%s" must be "%s" ("%s" given).', $name, $option->getType(), is_object($value) ? get_class($value) : gettype($value)));
            }

            if ($constraints) {
                /** @var ConstraintViolationInterface[] */
                $constraintsViolations = iterator_to_array($validator->validate($value, $constraints));

                foreach ($constraintsViolations as $constraintViolation) {
                    $errors[$name][] = new TranslatableMessage($constraintViolation->getMessageTemplate(), $constraintViolation->getParameters());
                }
            }

            if ($value instanceof Result && !$value->isValid()) {
                foreach ($value->getErrors() as $childErrors) {
                    $errors[$name] = array_merge($errors[$name] ?? [], $childErrors);
                }
            }

            if (empty($errors)) {
                foreach ($customValidators as $customValidator) {
                    $customValidatorResult = $customValidator['validator']($value, $option->getDefault(), $this->data);

                    if (false === $customValidatorResult || (is_array(($customValidatorResult) && !empty($customValidatorResult)))) {
                        $errors[$name][] = new TranslatableMessage($customValidator['error'], is_array($customValidatorResult) ? $customValidatorResult : []);
                        break;
                    }
                }
            }
        }

        $this->errors = array_filter($errors);
    }

    /**
     * Converts value to specific type.
     */
    private function convertValue(mixed $value, ?string $type, bool $isNullable = false): mixed
    {
        // Return original value
        if (!$type || gettype($value) === $type || (is_object($value) && get_class($value) === $type)) {
            return is_string($value) ? trim($value) : $value;
        }

        // Return null if null is allowed
        elseif ($isNullable && !$value) {
            return null;
        }

        // Convert to datetime
        elseif (Option::TYPE_DATETIME === $type || \DateTime::class === $type) {
            return $value && is_string($value) ? new \DateTime($value) : $value;
        }

        // Convert to date
        elseif (Option::TYPE_DATE === $type) {
            return $value && is_string($value) ? (new \DateTime($value))->setTime(0, 0, 0, 0) : $value;
        }

        // Convert to boolean
        elseif (Option::TYPE_BOOL === $type) {
            return ('0' === $value || 'false' === strtolower($value)) ? false : (bool) $value;
        }

        // Convert to array
        elseif (Option::TYPE_INT === $type) {
            return (int) $value;
        }

        // Convert to float
        elseif (Option::TYPE_FLOAT === $type) {
            return (float) $value;
        }

        // Convert to string
        elseif (Option::TYPE_STRING === $type) {
            return $value = trim((string) $value);
        }

        // Convert to array
        elseif (Option::TYPE_ARRAY === $type || '[]' === substr($type, -2)) {
            if ($value instanceof Collection) {
                $value = $value->toArray();
            } else {
                $value = is_array($value) ? $value : (null !== $value ? [$value] : []);
            }

            if ('[]' === substr($type, -2)) {
                $value = array_map(function ($item) use ($type) {
                    return $this->convertValue($item, substr($type, 0, -2));
                }, $value);
            }

            return $value;
        }

        // Return not converted value
        return $value;
    }

    /**
     * Checks if type value is correct.
     */
    private function verifyType(mixed $value, ?string $type, bool $isNullable = false): bool
    {
        if ($isNullable && null === $value) {
            return true;
        } elseif (null === $type) {
            return true;
        } elseif ('[]' === substr($type, -2)) {
            return !array_filter($value ?: [], function ($item) use ($type) {
                return !$this->verifyType($item, substr($type, 0, -2));
            }) ? true : false;
        } elseif (gettype($value) === $type) {
            return true;
        } elseif (is_object($value) && class_exists($type) && $value instanceof $type) {
            return true;
        } elseif ($value instanceof \DateTime && in_array($type, ['date', 'datetime'])) {
            return true;
        }

        return false;
    }
}
