<?php

declare(strict_types = 1);

namespace Graphpinator\Exception\Type;

final class FieldConstraintNotContravariant extends \Graphpinator\Exception\Type\TypeError
{
    public const MESSAGE = 'Field constraint is not contravariant.';
}
