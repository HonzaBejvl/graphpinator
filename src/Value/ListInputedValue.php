<?php

declare(strict_types = 1);

namespace Graphpinator\Value;

final class ListInputedValue implements \Graphpinator\Value\InputedValue, \Graphpinator\Value\ListValue, \Iterator, \ArrayAccess
{
    use \Nette\SmartObject;

    private \Graphpinator\Type\ListType $type;
    private array $value;

    public function __construct(\Graphpinator\Type\ListType $type, array $rawValue)
    {
        $innerType = $type->getInnerType();
        \assert($innerType instanceof \Graphpinator\Type\Contract\Inputable);

        $value = [];

        foreach ($rawValue as $item) {
            $value[] = $innerType->createInputedValue($item);
        }

        $this->type = $type;
        $this->value = $value;
    }

    public function getRawValue() : array
    {
        $return = [];

        foreach ($this->value as $listItem) {
            \assert($listItem instanceof InputedValue);

            $return[] = $listItem->getRawValue();
        }

        return $return;
    }

    public function getType() : \Graphpinator\Type\ListType
    {
        return $this->type;
    }

    public function printValue(bool $prettyPrint = false, int $indentLevel = 1) : string
    {
        if ($prettyPrint) {
            return $this->prettyPrint($indentLevel);
        }

        $component = [];

        foreach ($this->value as $value) {
            \assert($value instanceof InputedValue);

            $component[] = $value->printValue();
        }

        return '[' . \implode(',', $component) . ']';
    }

    public function current() : InputedValue
    {
        return \current($this->value);
    }

    public function next() : void
    {
        \next($this->value);
    }

    public function key() : int
    {
        return \key($this->value);
    }

    public function valid() : bool
    {
        return \key($this->value) !== null;
    }

    public function rewind() : void
    {
        \reset($this->value);
    }

    private function prettyPrint(int $indentLevel = 1) : string
    {
        if (\count($this->value) === 0) {
            return '[]';
        }

        $component = [];
        $indent = \str_repeat('  ', $indentLevel);
        $innerIndent = $indent . '  ';

        foreach ($this->value as $value) {
            \assert($value instanceof InputedValue);

            $component[] = $value->printValue(true, $indentLevel + 1);
        }

        return '[' . \PHP_EOL . $innerIndent . \implode(',' . \PHP_EOL . $innerIndent, $component) . \PHP_EOL . $indent . ']';
    }

    public function offsetExists($offset) : bool
    {
        return \array_key_exists($offset, $this->value);
    }

    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    public function offsetSet($offset, $value) : void
    {
        $this->value[$offset] = $value;
    }

    public function offsetUnset($offset) : void
    {
        unset($this->value[$offset]);
    }
}
