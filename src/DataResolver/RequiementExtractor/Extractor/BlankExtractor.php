<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Blank;

class BlankExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Blank::class;

    public function extract(): array
    {
        return [
            'required' => false
        ];
    }
}
