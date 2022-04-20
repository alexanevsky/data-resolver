<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Symfony\Component\Validator\Constraints\Url;

class UrlExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = Url::class;

    public function extract(): array
    {
        return [
            'protocols' =>          $this->constraint->protocols,
            'relative_protocol' =>  $this->constraint->relativeProtocol
        ];
    }
}
