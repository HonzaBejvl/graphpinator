<?php

declare(strict_types = 1);

namespace Graphpinator\Argument;

final class Argument
{
    use \Nette\SmartObject;

    private string $name;
    private ?string $description;
    private \Graphpinator\Type\Contract\Inputable $type;
    private ?\Graphpinator\Value\ValidatedValue $defaultValue;

    public function __construct(string $name, \Graphpinator\Type\Contract\Inputable $type, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;

        if (\func_num_args() === 3) {
            $defaultValue = $type->createValue($defaultValue);
        }

        $this->defaultValue = $defaultValue;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function getType() : \Graphpinator\Type\Contract\Inputable
    {
        return $this->type;
    }

    public function getDefaultValue() : ?\Graphpinator\Value\ValidatedValue
    {
        return $this->defaultValue;
    }
}
