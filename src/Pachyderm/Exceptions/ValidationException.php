<?php

namespace Pachyderm\Exceptions;

class ValidationException extends AbstractHTTPException
{
    protected int $code = 400;
    public array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }
}
