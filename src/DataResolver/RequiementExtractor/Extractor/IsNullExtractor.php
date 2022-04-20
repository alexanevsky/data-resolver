<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Type;

class IsNullExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = IsNull::class;

    public function extract(): array
    {
        return [
            'nullable' => true
        ];
    }
}
