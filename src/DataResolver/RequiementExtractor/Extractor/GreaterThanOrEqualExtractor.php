<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class GreaterThanOrEqualExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = GreaterThanOrEqual::class;

    public function extract(): array
    {
        return [
            'min' => $this->constraint->value
        ];
    }
}
