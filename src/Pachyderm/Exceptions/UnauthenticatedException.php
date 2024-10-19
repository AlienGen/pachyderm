<?php

namespace Pachyderm\Exceptions;

class UnauthenticatedException extends AbstractHTTPException
{
    protected $code = 401;
}
