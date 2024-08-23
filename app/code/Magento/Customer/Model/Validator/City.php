<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\GlobalCityValidator;

/**
 * Customer city fields validator.
 */
class City extends AbstractValidator
{
    /**
     * Validate city fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer): bool
    {
        if (!GlobalCityValidator::isValidCity($customer->getCity())) {
            parent::_addMessages([[
                'city' => __("Invalid City. Please use only A-Z, a-z, 0-9, spaces, commas, -, ., ', &, [], ().")
            ]]);
        }

        return count($this->_messages) == 0;
    }
}
