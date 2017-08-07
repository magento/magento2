<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Address;

/**
 * Generate address data for customer
 * @since 2.2.0
 */
class AddressDataGenerator
{
    /**
     * Generate address data
     *
     * @return array
     * @since 2.2.0
     */
    public function generateAddress()
    {
        return [
            'postcode' => mt_rand(10000, 99999)
        ];
    }
}
