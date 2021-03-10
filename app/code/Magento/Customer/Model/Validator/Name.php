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
    /**
     * Validate name fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer)
    {
        if (!$this->isValidName($customer->getFirstname())) {
            $this->_addErrorMessages('firstname', (array)['First Name is not valid!']);
        }

        if (!$this->isValidName($customer->getLastname())) {
            $this->_addErrorMessages('lastname', (array)['Last Name is not valid!']);
        }

        if (!$this->isValidName($customer->getMiddlename())) {
            $this->_addErrorMessages('middlename', (array)['Middle Name is not valid!']);
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
            $pattern = '/(?:[\p{L}\p{M}\,\-\_\.\'\"\s\d]){1,255}+/u';
            if (preg_match($pattern, $nameValue, $matches)) {
                return $matches[0] == $nameValue;
            }
        }

        return true;
    }

    /**
     * Add error messages.
     *
     * @param string $code
     * @param array $messages
     * @return void
     */
    protected function _addErrorMessages($code, array $messages)
    {
        if (!array_key_exists($code, $this->_messages)) {
            $this->_messages[$code] = $messages;
        } else {
            $this->_messages[$code] = array_merge($this->_messages[$code], $messages);
        }
    }
}
