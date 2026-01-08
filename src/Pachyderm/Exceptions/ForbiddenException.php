<?php

namespace Pachyderm\Exceptions;

class ForbiddenException extends AbstractHTTPException
{
    protected $code = 403;
}
