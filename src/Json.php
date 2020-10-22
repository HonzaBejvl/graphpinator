<?php

declare(strict_types = 1);

namespace Graphpinator;

final class Json implements \Countable, \IteratorAggregate, \ArrayAccess, \Serializable
{
    use \Nette\SmartObject;

    private const FLAGS = \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;

    private ?string $string;
    private ?\stdClass $data;

    private function __construct(?string $json, ?\stdClass $data)
    {
        $this->string = $json;
        $this->data = $data;
    }

    public static function fromString(string $json) : self
    {
        return new static($json, null);
    }

    public static function fromObject(\stdClass $data) : self
    {
        return new static(null, $data);
    }

    public function toString() : string
    {
        $this->loadString();

        return $this->string;
    }

    public function toObject() : \stdClass
    {
        $this->loadObject();

        return $this->data;
    }

    public function count() : int
    {
        $this->loadObject();

        return \count((array) $this->data);
    }

    public function getIterator() : \Iterator
    {
        $this->loadObject();

        return new \ArrayIterator($this->data);
    }

    public function offsetExists($offset) : bool
    {
        $this->loadObject();

        return \property_exists($this->data, $offset);
    }

    /** @return int|string|bool|array|\stdClass */
    public function offsetGet($offset)
    {
        $this->loadObject();

        return $this->data->{$offset};
    }

    public function offsetSet($offset, $value) : void
    {
        $this->loadObject();
        $this->data->{$offset} = $value;
        $this->string = null;
    }

    public function offsetUnset($offset) : void
    {
        $this->loadObject();
        unset($this->data->{$offset});
        $this->string = null;
    }

    public function serialize() : string
    {
        return $this->toString();
    }

    public function unserialize($serialized) : self
    {
        return self::fromString($serialized);
    }

    private function loadString() : void
    {
        if (\is_string($this->string)) {
            return;
        }

        $this->string = \json_encode($this->data, self::FLAGS);
    }

    private function loadObject() : void
    {
        if ($this->data instanceof \stdClass) {
            return;
        }

        $this->data = \json_decode($this->string, false, 512, self::FLAGS);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function __isset($offset) : bool
    {
        return $this->offsetExists($offset);
    }

    /** @return int|string|bool|array|\stdClass */
    public function __get($offset)
    {
        return $this->offsetGet($offset);
    }

    public function __set($offset, $value) : void
    {
        $this->offsetSet($offset, $value);
    }

    public function __unset($offset) : void
    {
        $this->offsetUnset($offset);
    }
}
