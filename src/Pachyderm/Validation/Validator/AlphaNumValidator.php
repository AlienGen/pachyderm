<?php

namespace Pachyderm\Validation\Validator;

use Pachyderm\Validation\ValidatorInterface;

class AlphaNumValidator implements ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool
    {
        return ctype_alnum($value);
    }

    public function getErrorMessage(array $options = []): string
    {
        return 'The value must contain only alphabetic characters and numbers.';
    }
}
