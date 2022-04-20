<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Length;

class LengthExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Length::class;

    public function extract(): array
    {
        return array_filter([
            'minlength' => $this->constraint->min,
            'maxlength' => $this->constraint->max
        ], function ($length) {return null !== $length;});
    }
}
