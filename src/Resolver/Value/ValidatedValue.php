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

    //@phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
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

    //@phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
    public function getRawValue()
    {
        return $this->value;
    }

    //@phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
    public function jsonSerialize()
    {
        return $this->value;
    }

    public function printValue() : string
    {
        return $this->executePrintValue();
    }

    private function executePrintValue(bool $isList = false) : string
    {
        if (!\is_array($this->value)) {
            return \json_encode($this->value, \JSON_THROW_ON_ERROR);
        }

        $outputValue = $isList ?
            '[' : '{';

        $lastKey = \array_key_last($this->value);

        foreach ($this->value as $key => $value) {
            $outputValue .= \is_int($key) ?
                '' : $key . ':';

            if (\is_array($value->value)) {
                $outputValue .= $value->executePrintValue(\array_key_first($value->value) === 0);
            } else {
                $outputValue .= $value->executePrintValue();
            }

            if ($lastKey === $key) {
                break;
            }

            $outputValue .= ',';
        }

        $outputValue .= $isList ?
            ']' : '}';

        return $outputValue;
    }
}
