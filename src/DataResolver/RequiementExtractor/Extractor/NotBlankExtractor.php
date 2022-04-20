<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\NotBlank;

class NotBlankExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = NotBlank::class;

    public function extract(): array
    {
        return [
            'required' => true
        ];
    }
}
