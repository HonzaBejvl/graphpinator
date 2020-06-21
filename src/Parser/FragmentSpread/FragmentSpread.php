<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

interface FragmentSpread
{
    public function normalize(
        \Graphpinator\Type\Container\Container $typeContainer,
        \Graphpinator\Parser\Fragment\FragmentSet $fragmentDefinitions
    ) : \Graphpinator\Normalizer\FragmentSpread\FragmentSpread;
}
