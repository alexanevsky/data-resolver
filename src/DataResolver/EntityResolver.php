<?php

namespace Alexanevsky\DataResolver;

use Alexanevsky\DataResolver\Exception\InvalidConfigurationException;
use Alexanevsky\DataResolver\Option\EntityOption;
use Alexanevsky\DataResolver\Result\EntityResult;
use Doctrine\Common\Collections\Collection;
use function Symfony\Component\String\u;

class EntityResolver extends Resolver
{
    /**
     * Normalizer of entity.
     * Calls in the end of entity handling.
     */
    protected ?\Closure $entityNormalizer = null;

    /**
     * The entity that was handled.
     */
    protected ?object $handledEntity = null;

    /**
     * {@inheritDoc}
     */
    public function resolve(array $data): EntityResult
    {
        return $this->resolveOptions(EntityResult::class, $data);
    }

    /**
     * {@inheritDoc}
     *
     * @return EntityOption[]
     */
    public function all(): array
    {
        return parent::all();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $name): EntityOption
    {
        return parent::get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function define(string $name, $type = null, bool $isNullable = null): EntityOption
    {
        return $this->set(EntityOption::class, $name, $type, $isNullable);
    }

    /**
     * Gets the entity normalizer.
     */
    public function getEntityNormalizer(): ?\Closure
    {
        return $this->entityNormalizer;
    }

    /**
     * Sets a entity normalizer.
     */
    public function setEntityNormalizer(?\Closure $normalizer): static
    {
        $this->entityNormalizer = $normalizer;

        return $this;
    }

    /**
     * Checks if entity normalizer is defined.
     */
    public function hasEntityNormalizer(): bool
    {
        return isset($this->entityNormalizer);
    }

    /**
     * Sets the defaults of values of options from entity properties values.
     */
    public function handleEntity(object $entity): static
    {
        $this->handledEntity = $entity;

        foreach ($this->all() as $name => $option) {
            if (!$option->isMapped()) {
                continue;
            }

            if (!$getter = $option->getGetter() ?: $this->parseGetter($this->handledEntity, $name)) {
                throw new InvalidConfigurationException(sprintf('Cannot get the property "%s" value because the class "%s" has not available getter.', $name, $this->handledEntity::class));
            }

            $default = $this->handledEntity->$getter();

            if ($option->hasGetterNormalizer()) {
                $default = $option->getGetterNormalizer()($default);
            }

            if ($default instanceof Collection) {
                $default = $default->toArray();
            }

            $option->setDefault($default);
        }

        return $this;
    }

    /**
     * Gets the entity that was handled.
     */
    public function getHandledEntity(): ?object
    {
        return $this->handledEntity;
    }

    /**
     * Detects the available getter of property by name.
     */
    private function parseGetter(object $entity, string $name): ?string
    {
        $name = u($name)->camel()->toString();

        if (method_exists($entity, $name)) {
            return $name;
        } elseif (method_exists($entity, 'get' . $name)) {
            return 'get' . $name;
        }

        return null;
    }
}
