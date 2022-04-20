<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\GreaterThan;

class GreaterThanExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = GreaterThan::class;

    public function extract(): array
    {
        return [
            'min' => $this->constraint->value + 1
        ];
    }
}
