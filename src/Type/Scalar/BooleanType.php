<?php

declare(strict_types = 1);

namespace Graphpinator\Type\Scalar;

final class BooleanType extends \Graphpinator\Type\Scalar\ScalarType
{
    protected const NAME = 'Boolean';
    protected const DESCRIPTION = 'Boolean built-in type';

    protected function validateNonNullValue($rawValue) : void
    {
        if (\is_bool($rawValue)) {
            return;
        }

        throw new \Exception();
    }
}
