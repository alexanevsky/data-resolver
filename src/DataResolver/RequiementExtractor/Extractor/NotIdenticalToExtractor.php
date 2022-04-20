<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\NotIdenticalTo;

class NotIdenticalToExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = NotIdenticalTo::class;

    public function extract(): array
    {
        return [
            'not' => [$this->constraint->value]
        ];
    }
}
