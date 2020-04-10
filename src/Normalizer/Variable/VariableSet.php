<?php

declare(strict_types = 1);

namespace Graphpinator\Normalizer\Variable;

final class VariableSet extends \Graphpinator\Utils\ClassSet
{
    public const INNER_CLASS = Variable::class;

    public function current() : Variable
    {
        return parent::current();
    }

    public function offsetGet($offset) : Variable
    {
        return parent::offsetGet($offset);
    }
}
