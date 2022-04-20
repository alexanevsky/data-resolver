<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\PositiveOrZero;

class PositiveOrZeroExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = PositiveOrZero::class;

    public function extract(): array
    {
        return [
            'min' => 0
        ];
    }
}
