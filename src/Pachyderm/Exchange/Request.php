<?php

namespace Pachyderm\Exchange;

use Pachyderm\Utils\IterableObjectSet;

class Request extends IterableObjectSet
{
    private $body;

    /**
     * @param mixed $body
     */
    public function __construct(mixed $body)
    {
        $this->body = $body;
    }

    public function body(): mixed {
        return $this->body;
    }

    public function __get($key)
    {
        return $this->body[$key];
    }

    public function __set($key, $value)
    {
        $this->body[$key] = $value;
    }
}
