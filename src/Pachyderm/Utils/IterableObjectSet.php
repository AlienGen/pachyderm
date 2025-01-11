<?php

namespace Pachyderm\Utils;

class IterableObjectSet implements \Countable, \Iterator, \ArrayAccess, \JsonSerializable
{
    protected array $_data = array();

    /**
     * Magic getter to get the value of a key.
     */
    public function __get($key)
    {
        if (!isset($this->_data[$key])) {
            return null;
        }
        return $this->_data[$key];
    }

    /**
     * Magic setter to set the value of a key
     */
    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Magic isset to define the behavior of the isset method.
     */
    public function __isset(string $key): bool
    {
        return isset($this->_data[$key]);
    }

    /**
     * Count the number of elements in the sett
     */
    public function count(): int
    {
        return count($this->_data);
    }

    /**
     * Iterator current
     */
    public function current(): mixed
    {
        return current($this->_data);
    }

    /**
     * Iterator next
     */
    public function next(): void
    {
        next($this->_data);
    }

    /**
     * Iterator key
     */
    public function key(): null|string|int
    {
        return key($this->_data);
    }

    /**
     * Iterator valid
     */
    public function valid(): bool
    {
        return key($this->_data) !== null;
    }

    /**
     * Iterator rewind
     */
    public function rewind(): void
    {
        reset($this->_data);
    }

    /**
     * Array like setter
     */
    public function offsetSet($key, $value): void
    {
        $this->_data[$key] = $value;
    }

    /**
     * Array like exists
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * Array like unset
     */
    public function offsetUnset($key): void
    {
        unset($this->_data[$key]);
    }

    /**
     * Array like getter
     */
    public function offsetGet($key): mixed
    {
        return $this->_data[$key];
    }

    /**
     * Prepare serialization
     */
    public function jsonSerialize(): mixed
    {
        return $this->_data;
    }
}
