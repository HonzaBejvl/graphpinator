<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class NamedValueSet extends \Graphpinator\ClassSet
{
    public const INNER_CLASS = NamedValue::class;

    public function current() : NamedValue
    {
        return parent::current();
    }

    public function offsetGet($offset) : NamedValue
    {
        return parent::offsetGet($offset);
    }

    public function applyVariables(\Graphpinator\Request\VariableValueSet $variables) : self
    {
        $values = [];

        foreach ($this as $value) {
            $values[] = $value->applyVariables($variables);
        }

        return new self($values);
    }
}
