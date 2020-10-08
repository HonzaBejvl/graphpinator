<?php

declare(strict_types = 1);

namespace Graphpinator\Value;

final class ListInputedValue implements InputedValue, ListValue, \Iterator
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

    public function printValue() : string
    {
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
}
