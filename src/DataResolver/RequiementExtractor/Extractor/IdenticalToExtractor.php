<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\IdenticalTo;

class IdenticalToExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = IdenticalTo::class;

    public function extract(): array
    {
        return [
            'identical' => $this->constraint->value
        ];
    }
}
