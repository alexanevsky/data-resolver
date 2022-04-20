<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\LessThan;

class LessThanExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = LessThan::class;

    public function extract(): array
    {
        return [
            'max' => $this->constraint->value - 1
        ];
    }
}
