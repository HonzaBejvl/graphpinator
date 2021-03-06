<?php

declare(strict_types = 1);

namespace Graphpinator\Exception\Normalizer;

final class MisplacedDirective extends \Graphpinator\Exception\Normalizer\NormalizerError
{
    public const MESSAGE = 'Directive cannot be used on this DirectiveLocation.';
}
