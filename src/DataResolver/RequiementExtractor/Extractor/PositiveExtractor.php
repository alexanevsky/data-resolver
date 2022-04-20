<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Positive;

class PositiveExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Positive::class;

    public function extract(): array
    {
        return [
            'min' => 1
        ];
    }
}
