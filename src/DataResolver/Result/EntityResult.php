<?php

namespace Alexanevsky\DataResolver\Result;

use Alexanevsky\DataResolver\Resolver;
use Alexanevsky\DataResolver\EntityResolver;
use function Symfony\Component\String\u;

class EntityResult extends Result
{
    /**
     * {@inheritDoc}
     *
     * @var EntityResolver
     */
    protected Resolver $resolver;

    /**
     * Sets the values of options to given entity.
     */
    public function handleEntity(?object $entity = null): void
    {
        $entity ??= $this->resolver->getHandledEntity();

        foreach ($this->toArray() as $name => $value) {
            $option = $this->resolver->get($name);

            if (!$option->isMapped()) {
                continue;
            }

            if (!$setter = $option->getSetter() ?: $this->parseSetter($entity, $name)) {
                throw new \Exception(sprintf('Cannot set the property "%s" because the class "%s" has not available setter.', $name, $entity::class));
            }

            if ($option->hasSetterNormalizer()) {
                $value = $option->getSetterNormalizer()($value);
            }

            $entity->$setter($value);
        }

        if ($this->resolver->hasEntityNormalizer()) {
            $this->resolver->getEntityNormalizer()($entity, $this->toArray());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getResolver(): EntityResolver
    {
        return $this->resolver;
    }

    /**
     * Detects the available setter of property by name.
     */
    private function parseSetter(object $entity, string $name): ?string
    {
        $name = u($name)->camel()->toString();

        if (method_exists($entity, 'set' . $name)) {
            return 'set' . $name;
        } elseif ('is' === substr($name, 0, 2) && method_exists($entity, 'set' . substr($name, 2))) {
            return 'set' . substr($name, 2);
        }

        return null;
    }
}
