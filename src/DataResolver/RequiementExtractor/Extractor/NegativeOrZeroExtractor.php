<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\NegativeOrZero;

class NegativeOrZeroExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = NegativeOrZero::class;

    public function extract(): array
    {
        return [
            'max' => 0
        ];
    }
}
