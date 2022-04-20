<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraint;

abstract class AbstractExtractor
{
    public const CONSTRAINT_CLASS = Constraint::class;

    public function __construct(
        protected Constraint $constraint
    )
    {}

    public function getConstraintClass(): string
    {
        return static::CONSTRAINT_CLASS;
    }

    abstract public function extract(): array;
}
