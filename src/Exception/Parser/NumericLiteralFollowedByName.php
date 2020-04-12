<?php

declare(strict_types = 1);

namespace Graphpinator\Exception\Parser;

final class NumericLiteralFollowedByName extends ParseError
{
    public const MESSAGE = 'Numeric literal cannot be followed by name.';
}
