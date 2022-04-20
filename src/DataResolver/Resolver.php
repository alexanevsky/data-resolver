<?php

namespace Alexanevsky\DataResolver;

use Alexanevsky\DataResolver\Exception\InvalidConfigurationException;
use Alexanevsky\DataResolver\Option\Option;
use Alexanevsky\DataResolver\RequiementExtractor\RequiementExtractor;
use Alexanevsky\DataResolver\Result\Result;

class Resolver
{
    /**
     * The defined options list.
     *
     * @var Option[]
    */
    protected array $options = [];

    /**
     * Allow or disallow type conversion of option values to a specific option type.
     */
    protected bool $isAllowedTypeConversion = true;

    /**
     * Normalizer of all data.
     * Calls after normalizing all options.
     */
    protected ?\Closure $normalizer = null;

    /**
     * Any data to store in resolver.
     */
    protected array $payload = [];

    /**
     * Resolves given data.
     */
    public function resolve(array $data): Result
    {
        return $this->resolveOptions(Result::class, $data);
    }

    /**
     * Gets all defined options.
     *
     * @return Option[]
     */
    public function all(): array
    {
        return $this->options;
    }

    /**
     * Gets the defined option by name.
     */
    public function get(string $name): Option
    {
        if (!isset($this->options[$name])) {
            throw new InvalidConfigurationException(sprintf('The option "%s" is not defined.', $name));
        }

        return $this->options[$name];
    }

    /**
     * Defines a new option.
     */
    public function define(string $name, $type = null, bool $isNullable = null): Option
    {
        return $this->set(Option::class, $name, $type, $isNullable);
    }

    /**
     * Checks if option is defined.
     */
    public function has(string $name): bool
    {
        return isset($this->options[$name]);
    }

    /**
     * Removes defined option.
     */
    public function unset(string $name): static
    {
        if (isset($this->options[$name])) {
            unset($this->options[$name]);
        }

        return $this;
    }

    /**
     * Gets names of the options.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->options);
    }

    /**
     * Gets default values of defined options.
     */
    public function getDefaults(): array
    {
        return array_map(function (Option $option) {
            return $option->getDefault();
        }, $this->options);
    }

    /**
     * Gets requirements and default values of defined options.
     */
    public function getRequirements(): array
    {
        $output = [];

        foreach ($this->options as $name => $option) {
            $output[$name] = [
                'name' =>           $name,
                'value' =>          $option->getDefaultConverted(),
                'requirements' =>   array_merge(['required' => false], (new RequiementExtractor($option->getConstraints()))->extract())
            ];
        }

        return $output;
    }

    /**
     * Checks if the type conversion of option values to a specific option type is allowed.
     */
    public function isAllowedTypeConversion(): bool
    {
        return $this->isAllowedTypeConversion;
    }

    /**
     * Sets whether type conversion of option values to a specific option type is allowed.
     */
    public function setAllowedTypeConversion(bool $isAllowedTypeConversion): static
    {
        $this->isAllowedTypeConversion = $isAllowedTypeConversion;

        return $this;
    }

    /**
     * Gets the normalizer of all data.
     */
    public function getNormalizer(): ?\Closure
    {
        return $this->normalizer;
    }

    /**
     * Sets a normalizer of all data.
     */
    public function setNormalizer(?\Closure $normalizer): self
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * Checks if normalizer of all data is defined.
     */
    public function hasNormalizer(): bool
    {
        return isset($this->normalizer);
    }

    /**
     * Gets the payload.
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Gets the item of payload.
     */
    public function getPayloadItem(string $key): mixed
    {
        return $this->payload[$key] ?? null;
    }

    /**
     * Adds the item to payload.
     */
    public function addPayload(string $key, $value): static
    {
        $this->payload[$key] = $value;

        return $this;
    }

    /**
     * Removes the item to payload.
     */
    public function removePayload(string $key): static
    {
        if (isset($this->payload[$key])) {
            unset($this->payload[$key]);
        }

        return $this;
    }

    /**
     * Sets a new option.
     *
     * @param Resolver|string|null $type
     */
    protected function set(string $class, string $name, $type = null, bool $isNullable = null): Option
    {
        if (isset($this->options[$name])) {
            throw new InvalidConfigurationException(sprintf('The option "%s" cannot be defined twice.', $name));
        } elseif (null !== $type && !is_string($type) && !$type instanceof Resolver) {
            throw new InvalidConfigurationException(sprintf('Can not define option "%s" with incorrect type. Type can be a string, null or an instance of "%s"', $name, Resolver::class));
        } elseif (!class_exists($class) || !($class === Option::class || is_subclass_of($class, Option::class))) {
            throw new InvalidConfigurationException(sprintf('Can not define option "%s" with class "%s", only instances of "%s" must be given.', $name, $class, Option::class));
        }

        /** @var Option */
        $option = new $class();

        if (null !== $type) {
            $option->setType($type);
        }

        if (null !== $isNullable) {
            $option->setNullable($isNullable);
        }

        $this->options[$name] = $option;

        return $this->options[$name];
    }

    /**
     * Resolves given data.
     */
    protected function resolveOptions(string $class, array $data): Result
    {
        if (!class_exists($class)) {
            throw new InvalidConfigurationException(sprintf('Cannot resolve option with class of results "%s", only instances of "%s" must be given.', $class, Result::class));
        }

        return new $class($this, $data);
    }
}
