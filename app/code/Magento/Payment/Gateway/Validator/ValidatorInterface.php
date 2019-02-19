<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

/**
 * Interface ValidatorInterface
 * @package Magento\Payment\Gateway\Validator
 * @api
 * @since 100.0.2
 */
interface ValidatorInterface
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject);
}
