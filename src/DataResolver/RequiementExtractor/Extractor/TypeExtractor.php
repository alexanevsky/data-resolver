<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Type;

class TypeExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Type::class;

    public function extract(): array
    {
        return [
            'type' => $this->constraint->type
        ];
    }
}
