<?php

namespace Pachyderm\Exchange;

use Pachyderm\Utils\IterableObjectSet;

class Request extends IterableObjectSet
{
    private mixed $_body;
    /**
     * @param mixed $body
     */
    public function __construct(mixed $body)
    {
        $this->_body = $body;
        if (is_array($body)) {
            $this->_data = $body;
        }
    }

    public function body(): mixed {
        return $this->_body;
    }
}
