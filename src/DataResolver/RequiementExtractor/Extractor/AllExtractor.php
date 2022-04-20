<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Alexanevsky\DataResolver\RequiementExtractor\RequiementExtractor;
use Symfony\Component\Validator\Constraints\All;

class AllExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = All::class;

    public function extract(): array
    {
        return [
            'each' => (new RequiementExtractor($this->constraint->constraints))->extract()
        ];
    }
}
