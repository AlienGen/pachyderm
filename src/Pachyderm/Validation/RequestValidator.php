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
     * The returned array should be an array where keys are field names and values are validation rules.
     * 
     * Here is an example of the rules array:
     * [
     *     'email' => 'required|email',
     *     'user' => '', // This will be validated as an optional field. The nested fields will not be validated if the user field is not present.
     *     'user.name' => 'required|min:10',
     *     'user.age' => 'required|integer|min:18|max:65',
     * ]
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

        // Sort fields by depth to ensure parents are processed before children
        uksort($fieldsRules, function($a, $b) {
            return substr_count($a, '.') <=> substr_count($b, '.');
        });

        $errors = [];
        foreach ($fieldsRules as $field => $rules) {
            // Handle nested fields
            $parentKey = $this->getParentKey($field);
            $parentValue = $parentKey !== null ? $this->getNestedValue($body, $parentKey) : null;

            // Skip validation for nested fields if the parent does not exist
            if ($parentValue === null && strpos($field, '.') !== false) {
                continue;
            }

            $value = $this->getNestedValue($body, $field);

            // Validate the field if there are rules defined
            if (!empty($rules)) {
                // Store validation errors for the field, if any
                $fieldErrors = Validator::validate($rules, $value);
                if (!empty($fieldErrors)) {
                    $errors[$field] = $fieldErrors;
                }
            }

            // Assign the value to the corresponding property of the class
            $this->setNestedValue($field, $value);
        }

        // If there are any validation errors, throw a ValidationException
        if (count($errors) > 0) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    /**
     * Retrieve a nested value from an array using a dot notation key.
     * 
     * @param array $array The array to search.
     * @param string $key The dot notation key.
     * 
     * @return mixed The value found, or null if not found.
     */
    private function getNestedValue(array $array, string $key)
    {
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (!(is_array($array) && array_key_exists($k, $array))) {
                return null;
            }
            $array = $array[$k];
        }
        return $array;
    }

    /**
     * Set a nested value in the object using a dot notation key.
     * 
     * @param string $key The dot notation key.
     * @param mixed $value The value to set.
     */
    private function setNestedValue(string $key, mixed $value)
    {
        $keys = explode('.', $key);
        $current = &$this;
        $lastKey = array_pop($keys);

        foreach ($keys as $k) {
            if (!isset($current->$k) || !is_object($current->$k)) {
                $current->$k = new IterableObjectSet();
            }
            $current = &$current->$k;
        }

        // Assign the value to the final key
        $current->$lastKey = $value;
    }

    /**
     * Get the parent key from a dot notation key.
     * 
     * @param string $key The dot notation key.
     * 
     * @return string|null The parent key or null if there is no parent.
     */
    private function getParentKey(string $key): ?string
    {
        $lastDotPosition = strrpos($key, '.');
        return $lastDotPosition !== false ? substr($key, 0, $lastDotPosition) : null;
    }
}
