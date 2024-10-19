<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class TimeValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return (bool) preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/', $value);
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must be a time.';
    }
}
