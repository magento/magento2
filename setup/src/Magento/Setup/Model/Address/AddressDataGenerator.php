<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Address;

/**
 * Generate address data for customer
 */
class AddressDataGenerator
{
    /**
     * Generate address data
     *
     * @return array
     */
    public function generateAddress()
    {
        return [
            // mt_rand() here is not for cryptographic use.
            // phpcs:ignore Magento2.Security.InsecureFunction
            'postcode' => mt_rand(10000, 99999)
        ];
    }
}
