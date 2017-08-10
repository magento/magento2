<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validation;

use Magento\Framework\Exception\ValidatorException;

/**
 * Add possibility to set several messages to exception
 */
class ValidationException extends ValidatorException
{
    /**
     * @var array
     */
    private $errors;

    /**
     * @param array $errors
     * @param \Exception $previous
     */
    public function __construct(array $errors = [], \Exception $previous = null)
    {
        // TODO: remove logic
        $errorsCount = count($errors);
        if ($errorsCount) {
            $message = $errorsCount == 1 ? reset($errors) : __(implode('; ', $errors));
        } else {
            $message = __('Entity isn\'t valid.');
        }
        parent::__construct($message, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
