<?php

namespace Pachyderm\Exchange;

use Pachyderm\Utils\IterableObjectSet;

class Request extends IterableObjectSet
{
    /**
     * @param mixed $body
     */
    public function __construct(mixed $body)
    {
        $this->_data = $body;
    }

    public function body(): mixed {
        return $this->_data;
    }
}
