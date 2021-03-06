<?php

declare(strict_types = 1);

namespace Graphpinator\Exception\Normalizer;

final class InvalidFragmentType extends \Graphpinator\Exception\Normalizer\NormalizerError
{
    public const MESSAGE = 'Invalid fragment type condition. ("%s" is not instance of "%s").';

    public function __construct(string $childType, string $parentType)
    {
        $this->messageArgs = [$childType, $parentType];

        parent::__construct();
    }
}
