<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Customer email field validator.
 */
class Email extends AbstractValidator
{
    /**
     * Maximum length of customer email field in database.
     *
     * @see app/code/Magento/Customer/etc/db_schema.xml
     */
    private const MAX_EMAIL_LENGTH = 255;

    /**
     * Validate customer email length.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer)
    {
        $email = $customer->getEmail();
        if ($email !== null && strlen($email) > self::MAX_EMAIL_LENGTH) {
            parent::_addMessages([[
                'email' => (string) __(
                    '"%1" length must be equal or less than %2 characters.',
                    __('Email'),
                    self::MAX_EMAIL_LENGTH
                ),
            ]]);
        }

        return count($this->_messages) === 0;
    }
}
