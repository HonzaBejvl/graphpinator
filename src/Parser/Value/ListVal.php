<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class ListVal implements Value
{
    use \Nette\SmartObject;

    private array $value;

    public function __construct(array $value)
    {
        $this->value = $value;
    }

    public function normalize(\Graphpinator\Value\ValidatedValueSet $variables) : array
    {
        $return = [];

        foreach ($this->value as $key => $value) {
            \assert($value instanceof Value);

            $return[$key] = $value->normalize($variables);
        }

        return [];
    }
}