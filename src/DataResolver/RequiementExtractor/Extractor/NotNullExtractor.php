<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\NotNull;

class NotNullExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = NotNull::class;

    public function extract(): array
    {
        return [
            'nullable' => false
        ];
    }
}
