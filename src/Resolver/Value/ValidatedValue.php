<?php

declare(strict_types = 1);

namespace Graphpinator\Resolver\Value;

abstract class ValidatedValue implements \JsonSerializable
{
    use \Nette\SmartObject;

    protected \Graphpinator\Type\Contract\Definition $type;
    protected $value;

    protected function __construct($value, \Graphpinator\Type\Contract\Definition $type)
    {
        $this->value = $value;
        $this->type = $type;
    }

    public static function create($rawValue, \Graphpinator\Type\Contract\Definition $type)
    {
        if ($rawValue === null) {
            return new \Graphpinator\Resolver\Value\NullValue($type);
        }

        if ($type instanceof \Graphpinator\Type\Contract\Inputable && $type->isInputable()) {
            $rawValue = $type->applyDefaults($rawValue);
        }

        return new static($rawValue, $type);
    }

    public function getType() : \Graphpinator\Type\Contract\Definition
    {
        return $this->type;
    }

    public function getRawValue()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function printValue() : string
    {
        return \json_encode($this->value, JSON_THROW_ON_ERROR, 512);
    }
}
