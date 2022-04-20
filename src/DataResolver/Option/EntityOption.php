<?php

namespace Alexanevsky\DataResolver\Option;

class EntityOption extends Option
{
    /**
     * Allow or disallow set and get entity property via calling setter or getter.
     */
    protected bool $isMapped = true;

    /**
     * The getter of entity property.
     *
     * If not defined, tries to
     * call a method identical to the property
     * or prepend "get".
     */
    protected ?string $getter = null;

    /**
     * The setter of entity property.
     *
     * If not defined, tries to
     * call a method identical to the property,
     * prepend "set"
     * or replace "is" with "set" if the property starts with "is".
     */
    protected ?string $setter = null;

    /**
     * The normalizer of value received from the getter.
     */
    protected ?\Closure $getterNormalizer = null;

    /**
     * The normalizer of value provided to the setter.
     */
    protected ?\Closure $setterNormalizer = null;

    /**
     * Checks if mapping is allowed.
     */
    public function isMapped(): bool
    {
        return $this->isMapped;
    }

    /**
     * Sets allowing or disallowing of mapping.
     */
    public function setMapped(bool $isMapped): static
    {
        $this->isMapped = $isMapped;

        return $this;
    }

    /**
     * Gets the entity property getter.
     */
    public function getGetter(): ?string
    {
        return $this->getter;
    }

    /**
     * Sets the entity property getter.
     */
    public function setGetter(string $getter): static
    {
        $this->getter = $getter;

        return $this;
    }

    /**
     * Gets the entity property setter.
     */
    public function getSetter(): ?string
    {
        return $this->setter;
    }

    /**
     * Sets the entity property setter.
     */
    public function setSetter(string $setter): static
    {
        $this->setter = $setter;

        return $this;
    }

    /**
     * Gets the getter normalizer.
     */
    public function getGetterNormalizer(): ?\Closure
    {
        return $this->getterNormalizer;
    }

    /**
     * Sets a getter normalizer.
     */
    public function setGetterNormalizer(?\Closure $normalizer): static
    {
        $this->getterNormalizer = $normalizer;

        return $this;
    }

    /**
     * Checks if getter normalizer is defined.
     */
    public function hasGetterNormalizer(): bool
    {
        return isset($this->getterNormalizer);
    }

    /**
     * Gets the setter normalizer.
     */
    public function getSetterNormalizer(): ?\Closure
    {
        return $this->setterNormalizer;
    }

    /**
     * Sets a setter normalizer.
     */
    public function setSetterNormalizer(?\Closure $normalizer): static
    {
        $this->setterNormalizer = $normalizer;

        return $this;
    }

    /**
     * Checks if setter normalizer is defined.
     */
    public function hasSetterNormalizer(): bool
    {
        return isset($this->setterNormalizer);
    }
}
