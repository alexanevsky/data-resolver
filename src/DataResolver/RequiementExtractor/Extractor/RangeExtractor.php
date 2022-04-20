<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Range;

class RangeExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Range::class;

    public function extract(): array
    {
        return [
            'min' => $this->constraint->min,
            'max' => $this->constraint->max
        ];
    }
}
