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
 * @since 2.0.0
 */
interface ValidatorInterface
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return ResultInterface
     * @since 2.0.0
     */
    public function validate(array $validationSubject);
}
