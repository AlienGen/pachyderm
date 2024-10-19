<?php

namespace Pachyderm\Validation;

interface ValidatorInterface
{
    public function validate(mixed $value, array $options = []): bool;

    public function getErrorMessage(array $options = []): string;
}
