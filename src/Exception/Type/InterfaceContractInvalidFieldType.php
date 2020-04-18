<?php

declare(strict_types = 1);

namespace Graphpinator\Exception\Type;

final class InterfaceContractInvalidFieldType extends TypeError
{
    public const MESSAGE = 'Type doesnt satisfy interface - invalid field type';
}