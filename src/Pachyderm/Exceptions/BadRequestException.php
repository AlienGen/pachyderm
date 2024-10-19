<?php

namespace Pachyderm\Exceptions;

class BadRequestException extends AbstractHTTPException
{
    protected $code = 400;
}