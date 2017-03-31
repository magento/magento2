<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            'postcode' => mt_rand(10000, 99999)
        ];
    }
}
