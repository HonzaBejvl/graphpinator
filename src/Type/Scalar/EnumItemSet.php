<?php

declare(strict_types = 1);

namespace Graphpinator\Type\Scalar;

final class EnumItemSet extends \Infinityloop\Utils\ObjectSet
{
    protected const INNER_CLASS = EnumItem::class;

    public function current() : EnumItem
    {
        return parent::current();
    }

    public function offsetGet($offset) : EnumItem
    {
        return parent::offsetGet($offset);
    }

    protected function getKey($object)
    {
        return $object->getName();
    }
}
