<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Negative;

class NegativeExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Negative::class;

    public function extract(): array
    {
        return [
            'max' => -1
        ];
    }
}
