<?php

namespace Pachyderm\Exceptions;

abstract class AbstractHTTPException extends \Exception {
    protected $code = 500;

    public function getErrors() {
        return [];
    }
}