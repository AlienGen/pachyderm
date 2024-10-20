<?php

namespace Pachyderm\Exceptions;

class ValidationException extends AbstractHTTPException
{
    protected $code = 400;
    public array $errors;

    public function __construct(string $message, array $errors)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }
}
