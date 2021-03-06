<?php

declare(strict_types = 1);

namespace Graphpinator\Type\Contract;

trait TResolvable
{
    public function validateResolvedValue($rawValue) : void
    {
        if ($rawValue === null) {
            return;
        }

        if (!$this->validateNonNullValue($rawValue)) {
            throw new \Graphpinator\Exception\Value\InvalidValue($this->getName(), $rawValue);
        }
    }

    abstract protected function validateNonNullValue($rawValue) : bool;
}
