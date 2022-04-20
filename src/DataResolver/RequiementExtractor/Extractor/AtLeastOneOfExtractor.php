<?php

namespace Alexanevsky\DataResolver\RequiementExtractor\Extractor;

use Alexanevsky\DataResolver\RequiementExtractor\RequiementExtractor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\IdenticalTo;

class AtLeastOneOfExtractor extends AbstractExtractor
{
    public const CONSTRAINT_CLASS = AtLeastOneOf::class;

    public function extract(): array
    {
        $constraints = $this->constraint->constraints;
        $constraints = array_filter($constraints, function (Constraint $constraint) {return !$constraint instanceof Blank;});

        if (!array_filter($constraints, function (Constraint $constraint) {return !$constraint instanceof IdenticalTo;})) {
            return [
                'in' => array_values(array_map(function (IdenticalTo $constraint) {
                    return $constraint->value;
                }, $constraints))
            ];
        }

        return [
            'one_of' => (new RequiementExtractor($constraints))->extract()
        ];
    }
}
