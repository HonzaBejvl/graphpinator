<?php

declare(strict_types = 1);

namespace Graphpinator\Constraint;

abstract class ArgumentFieldConstraint implements \Graphpinator\Constraint\Constraint
{
    use \Nette\SmartObject;

    abstract public function print() : string;

    abstract public function validateType(\Graphpinator\Type\Contract\Definition $type) : bool;

    public function validate(\Graphpinator\Value\Value $value) : void
    {
        if ($value instanceof \Graphpinator\Value\NullValue) {
            return;
        }

        $this->validateFactoryMethod($value->getRawValue());
    }

    abstract protected function validateFactoryMethod($rawValue) : void;
}
