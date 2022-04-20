<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Email;

class EmailExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Email::class;

    public function extract(): array
    {
        return [
            'email' => true
        ];
    }
}
