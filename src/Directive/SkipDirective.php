<?php

declare(strict_types = 1);

namespace Graphpinator\Directive;

final class SkipDirective extends \Graphpinator\Directive\Directive
{
    protected const NAME = 'skip';
    protected const DESCRIPTION = 'Built-in skip directive.';

    public function __construct()
    {
        parent::__construct(
            new \Graphpinator\Argument\ArgumentSet([
                new \Graphpinator\Argument\Argument('if', \Graphpinator\Type\Container\Container::Boolean()->notNull()),
            ]),
            static function (\Graphpinator\Resolver\ArgumentValueSet $arguments) : string {
                return $arguments->offsetGet('if')->getRawValue()
                    ? DirectiveResult::SKIP
                    : DirectiveResult::NONE;
            },
            [
                DirectiveLocation::FIELD,
                DirectiveLocation::FRAGMENT_SPREAD,
                DirectiveLocation::INLINE_FRAGMENT,
            ],
            false,
        );
    }
}
