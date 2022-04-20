<?php

namespace Alexanevsky\DataResolver\RequiementExtractor;

use Alexanevsky\DataResolver\Exception\InvalidRequirementExtractionException;
use Alexanevsky\DataResolver\RequiementExtractor\Extractor\AbstractExtractor;
use Symfony\Component\Validator\Constraint;

class RequiementExtractor
{
    /**
     * @var Constraint[]
     */
    private array $constraints;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(array $constraints)
    {
        if (array_filter($constraints, function ($constraint) {return !$constraint instanceof Constraint;})) {
            throw new InvalidRequirementExtractionException('Array of constraints contains illegal elements.');
        }

        $this->constraints = $constraints;
    }

    public function extract(): array
    {
        $requirements = [];

        foreach ($this->constraints as $constraint) {
            $extractorClass = $this->detectExtractorClass($constraint);

            if (!$extractorClass) {
                continue;
            }

            /** @var AbstractExtractor */
            $extractor = new $extractorClass($constraint);

            if (!is_a($constraint, $extractor->getConstraintClass())) {
                continue;
            }

            $constraintRequirements = $extractor->extract();

            $requirements = isset(array_values($constraintRequirements)[0]) && is_array(array_values($constraintRequirements)[0])
                ? array_merge_recursive($requirements, $extractor->extract())
                : array_merge($requirements, $extractor->extract());
        }

        return $requirements;
    }

    private function detectExtractorClass(Constraint $constraint)
    {
        $constraintClass = get_class($constraint);
        $extractorClasses = [
            $constraintClass . 'Extractor',
            substr(self::class, 0, strrpos(self::class, '\\')) . '\Extractor' . substr($constraintClass, strrpos($constraintClass, '\\')) . 'Extractor'
        ];

        return array_values(array_filter($extractorClasses, function ($class) {return class_exists($class);}))[0] ?? null;
    }
}
