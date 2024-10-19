<?php

namespace Pachyderm\Validation;

use Pachyderm\Exceptions\ValidationException;
use Pachyderm\Exchange\Request;
use Pachyderm\Utils\IterableObjectSet;

/**
 * Abstract class RequestValidator
 * 
 * This class serves as a base for creating request validators. It extends
 * the Request class and requires implementing classes to define specific
 * validation rules for request fields. The constructor validates the request
 * body against these rules and throws a ValidationException if any validation
 * errors are detected.
 */
abstract class RequestValidator extends Request
{
    /**
     * Define validation rules for request fields.
     * 
     * @return array An associative array where keys are field names and values are validation rules.
     */
    public abstract function rules(): array;

    /**
     * Constructor for RequestValidator.
     * 
     * @param mixed $body The request body to be validated.
     * 
     * @throws ValidationException if validation errors are found.
     */
    public function __construct(mixed $body)
    {
        parent::__construct([]);
        
        // Retrieve validation rules from the implementing class
        $fieldsRules = $this->rules();

        $errors = [];
        foreach ($fieldsRules as $field => $rules) {
            // Get the value for the current field from the request body
            $value = $body[$field] ?? null;

            // Validate the field if there are rules defined
            if(!empty($rules)) {
                // Store validation errors for the field, if any
                $errors[$field] = Validator::validate($rules, $value);
            }

            // Assign the value to the corresponding property of the class
            $this->$field = $value;
        }

        // If there are any validation errors, throw a ValidationException
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }
}
