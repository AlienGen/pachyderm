<?php

namespace Pachyderm\Exceptions;

class NotFoundException extends AbstractHTTPException
{
    protected $code = 404;
}
