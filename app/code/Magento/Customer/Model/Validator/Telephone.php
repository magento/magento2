<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\GlobalPhoneValidation;

/**
 * Customer telephone fields validator.
 */
class Telephone extends AbstractValidator
{
    /**
     * Validate telephone fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer)
    {
        if (!GlobalPhoneValidation::isValidPhone($customer->getTelephone())) {
            parent::_addMessages([[
                'telephone' => __('Invalid Phone Number. Please use 0-9, +, -, (, ) and space.')
            ]]);
        }

        return count($this->_messages) == 0;
    }
}
