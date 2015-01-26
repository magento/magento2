<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * Exception to be thrown when there is an issue with the Input to a function call.
 */
class InputException extends AbstractAggregateException
{
    const DEFAULT_MESSAGE = 'One or more input exceptions have occurred.';
    const INVALID_FIELD_RANGE = 'The %fieldName value of "%value" must be between %minValue and %maxValue';
    const INVALID_FIELD_MIN_VALUE = 'The %fieldName value of "%value" must be greater than or equal to %minValue.';
    const INVALID_FIELD_MAX_VALUE = 'The %fieldName value of "%value" must be less than or equal to %maxValue.';
    const INVALID_FIELD_VALUE = 'Invalid value of "%value" provided for the %fieldName field.';
    const REQUIRED_FIELD = '%fieldName is a required field.';

    /**
     * Initialize the input exception.
     *
     * @param string     $message Exception message
     * @param array      $params  Substitution parameters
     * @param \Exception $cause   Cause of the InputException
     */
    public function __construct($message = self::DEFAULT_MESSAGE, $params = [], \Exception $cause = null)
    {
        parent::__construct($message, $params, $cause);
    }

    /**
     * Creates an InputException for when a specific field was provided with an invalid value.
     *
     * @param string $fieldName Name of the field which had an invalid value provided.
     * @param string $fieldValue The invalid value that was provided for the field.
     * @param \Exception $cause   Cause of the InputException
     * @return InputException
     */
    public static function invalidFieldValue($fieldName, $fieldValue, \Exception $cause = null)
    {
        return new InputException(
            self::INVALID_FIELD_VALUE,
            ['fieldName' => $fieldName, 'value' => $fieldValue],
            $cause
        );
    }

    /**
     * Creates an InputException for a missing required field.
     *
     * @param string $fieldName Name of the missing required field.
     * @return InputException
     */
    public static function requiredField($fieldName)
    {
        return new InputException(self::REQUIRED_FIELD, ['fieldName' => $fieldName]);
    }
}
