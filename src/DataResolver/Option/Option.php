<?php

namespace Alexanevsky\DataResolver\Option;

use Alexanevsky\DataResolver\Exception\InvalidOptionException;
use Alexanevsky\DataResolver\Resolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class Option
{
    public const TYPE_BOOL =    'boolean';
    public const TYPE_INT =     'integer';
    public const TYPE_FLOAT =   'double';
    public const TYPE_STRING =  'string';
    public const TYPE_ARRAY =   'array';
    public const TYPE_OBJECT =  'object';

    public const TYPE_DATE =        'date';
    public const TYPE_DATETIME =    'datetime';

    public const TYPE_ARRAY_OF_BOOLS =      'boolean[]';
    public const TYPE_ARRAY_OF_INTS =       'integer[]';
    public const TYPE_ARRAY_OF_FLOATS =     'double[]';
    public const TYPE_ARRAY_OF_STRINGS =    'string[]';
    public const TYPE_ARRAY_OF_ARRAYS =     'array[]';
    public const TYPE_ARRAY_OF_OBJECTS =    'object[]';

    public const TYPE_ARRAY_OF_DATES =      'date[]';
    public const TYPE_ARRAY_OF_DATETIMES =  'datetime[]';

    private const TYPES_ALIASES = [
        'int' =>        self::TYPE_INT,
        'bool' =>       self::TYPE_BOOL,
        'float' =>      self::TYPE_FLOAT,
        'int[]' =>      self::TYPE_ARRAY_OF_INTS,
        'bool[]' =>     self::TYPE_ARRAY_OF_BOOLS,
        'float[]' =>    self::TYPE_ARRAY_OF_FLOATS
    ];

    private const VALIDATION_ERROR = 'Validation failed!';

    private const FORMAT_DATETIME = 'c';

    private const FORMAT_DATE = 'Y-m-d';

    /**
     * The type of the option.
     * It can be a simple variable type (checked by function "gettype"),
     * a class name,
     * an array with defined items type (e.g. "int[]", "MySuperClass[]", etc.),
     * "datetime" and "date" as alias of \DateTime
     * or nested data resolver.
     *
     * @var Resolver|string|null
     */
    protected $type = null;

    /**
     * Default value of the option.
     * If it is not defined, default value will be empty for option type
     * or null if type is undefined or option is nullable.
     */
    protected mixed $default;

    /**
     * Allow or disallow nullable value.
     */
    protected bool $isNullable = false;

    /**
     * Normalizer of the option.
     * Calls before validation.
     */
    protected ?\Closure $normalizer = null;

    /**
     * Converter of default value to use in resolver requirements.
     */
    protected ?\Closure $defaultConverter = null;

    /**
     * Rules to option value validation.
     *
     * @var Constraint[]
     */
    protected array $constraints = [];

    /**
     * Array with custom validators.
     */
    protected array $validators = [];

    /**
     * Gets the allowed option type.
     *
     * @return Resolver|string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets allowed option type.
     */
    public function setType($type): static
    {
        if (is_string($type) && isset(self::TYPES_ALIASES[$type])) {
            $type = self::TYPES_ALIASES[$type];
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Checks if allowed type is defined.
     */
    public function hasType(): bool
    {
        return isset($this->type);
    }

    /**
     * Checks if the option type is nested data resolver.
     */
    public function isNested(): bool
    {
        return $this->type instanceof Resolver;
    }

    /**
     * Gets the default value.
     */
    public function getDefault(): mixed
    {
        // Return defined value
        if (isset($this->default)) {
            return $this->default;
        }

        // Return null if option is nullable
        if ($this->isNullable || !isset($this->type)) {
            return null;
        }

        // Return value for option with nested options
        if ($this->type instanceof Resolver) {
            return $this->isNullable ? null : $this->type->getDefaults();
        }

        // Return simple empty value
        if (self::TYPE_BOOL === $this->type) {
            return false;
        } elseif (self::TYPE_INT === $this->type) {
            return 0;
        } elseif (self::TYPE_FLOAT === $this->type) {
            return 0.0;
        } elseif (self::TYPE_STRING === $this->type) {
            return '';
        } elseif (self::TYPE_ARRAY === $this->type || '[]' === substr($this->type, -2)) {
            return [];
        }

        return null;
    }

    /**
     * Gets the default value converted to use in requirements.
     */
    public function getDefaultConverted(): mixed
    {
        $default = $this->getDefault();

        if ($this->hasDefaultConverter()) {
            return $this->getDefaultConverter()($default);
        } elseif ((self::TYPE_DATETIME === $this->type || \DateTime::class === $this->type) && $default instanceof \DateTime) {
            return $default->format(self::FORMAT_DATETIME);
        } elseif (self::TYPE_DATE === $this->type && $default instanceof \DateTime) {
            return $default->format(self::FORMAT_DATE);
        }

        return $default;
    }

    /**
     * Sets a default value.
     */
    public function setDefault(mixed $default): static
    {
        if ($this->type instanceof Resolver) {
            if (null === $default) {
                $this->default = null;
            } elseif (is_array($default)) {
                foreach ($default as $name => $value) {
                    if ($this->type->has($name)) {
                        $this->type->get($name)->setDefault($value);
                    }
                }
            } else {
                throw new InvalidOptionException('The default value for nested resolver must be a type of array or null.');
            }
        } else {
            $this->default = $default;
        }

        return $this;
    }

    /**
     * Checks if value can be null.
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * Sets a nullable value.
     */
    public function setNullable(bool $isNullable): static
    {
        $this->isNullable = $isNullable;

        return $this;
    }

    /**
     * Checks if Not Blank constraint exists in constraints array.
     */
    public function isRequired(): bool
    {
        return !empty(array_filter($this->constraints, function (Constraint $constraint) {return $constraint instanceof NotBlank;}));
    }

    /**
     * Adds or removes Not Blank constraint.
     */
    public function setRequied(bool $isRequired = true, ?string $message = null): static
    {
        if ($isRequired && !$this->isRequired()) {
            $this->constraints[] = new NotBlank(message: $message);
        } elseif (!$isRequired && $this->isRequired()) {
            foreach ($this->constraints as $key => $constraint) {
                if ($constraint instanceof NotBlank) {
                    unset($this->constraints[$key]);
                }
            }
        }

        return $this;
    }

    /**
     * Gets the normalizer.
     */
    public function getNormalizer(): ?\Closure
    {
        return $this->normalizer ?? null;
    }

    /**
     * Sets a normalizer.
     */
    public function setNormalizer(?\Closure $normalizer): static
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * Checks if normalizer is defined.
     */
    public function hasNormalizer(): bool
    {
        return isset($this->normalizer);
    }

    /**
     * Gets the converter of default value.
     */
    public function getDefaultConverter(): ?\Closure
    {
        return $this->defaultConverter ?? null;
    }

    /**
     * Sets a converter of default value.
     */
    public function setDefaultConverter(?\Closure $defaultConverter): static
    {
        $this->defaultConverter = $defaultConverter;

        return $this;
    }

    /**
     * Checks if converter of default value is defined.
     */
    public function hasDefaultConverter(): bool
    {
        return isset($this->defaultConverter);
    }

    /**
     * Gets the constraints.
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Sets constraints.
     *
     * @param Constraint[] $constraints
     */
    public function setConstraints(array $constraints): static
    {
        if (array_filter($constraints, function ($constraint) {return !$constraint instanceof Constraint;})) {
            throw new InvalidOptionException('Array of constraints contains illegal elements.');
        }

        $this->constraints = $constraints;

        return $this;
    }

    /**
     * Appends constraints.
     *
     * @param Constraint[] $constraints
     */
    public function addConstraints(array $constraints): static
    {
        if (array_filter($constraints, function ($constraint) {return !$constraint instanceof Constraint;})) {
            throw new InvalidOptionException('Array of constraints contains illegal elements.');
        }

        $this->constraints = array_merge($this->constraints, $constraints);

        return $this;
    }

    /**
     * Appends a constraint.
     */
    public function addConstraint(Constraint $constraint): static
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * Checks if constraints are defined.
     */
    public function hasConstraints(): bool
    {
        return !empty($this->constraints);
    }

    /**
     * Gets the validators.
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Appends a validator.
     */
    public function addValidator(\Closure $validator, string $error = self::VALIDATION_ERROR): static
    {
        $this->validators[] = [
            'validator' =>  $validator,
            'error' =>      $error
        ];

        return $this;
    }

    /**
     * Checks if validators are defined.
     */
    public function hasValidators(): bool
    {
        return !empty($this->validators);
    }
}
