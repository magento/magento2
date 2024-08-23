<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\GlobalStreetValidator;

/**
 * Customer street fields validator.
 */
class Street extends AbstractValidator
{
    /**
     * Validate street fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer): bool
    {
        foreach ($customer->getStreet() as $street) {
            if (!GlobalStreetValidator::isValidStreet($street)) {
                parent::_addMessages([[
                    'street' => __("Invalid Street Address. Please use only A-Z, a-z, 0-9, spaces, commas, -, ., ', &, [], ()")
                ]]);
            }
        }

        return count($this->_messages) == 0;
    }
}
