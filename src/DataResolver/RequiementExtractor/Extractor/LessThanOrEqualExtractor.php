<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class LessThanOrEqualExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = LessThanOrEqual::class;

    public function extract(): array
    {
        return [
            'max' => $this->constraint->value
        ];
    }
}
