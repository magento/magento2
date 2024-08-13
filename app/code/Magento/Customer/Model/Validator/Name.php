<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\GlobalNameValidator;

/**
 * Customer name fields validator.
 */
class Name extends AbstractValidator
{
    /**
     * Validate name fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer)
    {
        if (!GlobalNameValidator::isValidName($customer->getFirstname())) {
            parent::_addMessages([['firstname' => __('First Name is not valid!')]]);
        }

        if (!GlobalNameValidator::isValidName($customer->getLastname())) {
            parent::_addMessages([['lastname' => __('Last Name is not valid!')]]);
        }

        if (!GlobalNameValidator::isValidName($customer->getMiddlename())) {
            parent::_addMessages([['middlename' => __('Middle Name is not valid!')]]);
        }

        return count($this->_messages) == 0;
    }
}
