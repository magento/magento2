<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Customer name fields validator.
 */
class Name extends AbstractValidator
{
    private const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'’`&\s\d]){1,255}+/u';

    /**
     * Validate name fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer)
    {
        if (!$this->isValidName($customer->getFirstname())) {
            parent::_addMessages([['firstname' => 'First Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getLastname())) {
            parent::_addMessages([['lastname' => 'Last Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getMiddlename())) {
            parent::_addMessages([['middlename' => 'Middle Name is not valid!']]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * Check if name field is valid.
     *
     * @param string|null $nameValue
     * @return bool
     */
    private function isValidName($nameValue)
    {
        if ($nameValue != null) {
            if (preg_match(self::PATTERN_NAME, $nameValue, $matches)) {
                return $matches[0] == $nameValue;
            }
        }

        return true;
    }
}
