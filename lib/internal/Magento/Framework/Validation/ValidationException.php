<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validation;

use Magento\Framework\Exception\AbstractAggregateException;

/**
 * Add possibility to set several messages to exception
 *
 * @api
 */
class ValidationException extends AbstractAggregateException
{
    /**
     * @param ValidationResult $validationResult
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(ValidationResult $validationResult, \Exception $cause = null, $code = 0)
    {
        foreach ($validationResult->getErrors() as $error) {
            $this->addError($error);
        }
        parent::__construct($this->phrase, $cause, $code);
    }
}
