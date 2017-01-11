<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$addresses = [
    [
        'telephone' => 3234676,
        'postcode' => 47676,
        'country_id' => 'US',
        'city' => 'CityX',
        'street' => ['Black str, 48'],
        'lastname' => 'Smith',
        'firstname' => 'John',
        'address_type' => 'shipping',
        'email' => 'some_email@mail.com',
        'region_id' => 1,
    ],
    [
        'telephone' => 3234676,
        'postcode' => '47676',
        'country_id' => 'US',
        'city' => 'CityX',
        'street' => ['Black str, 48'],
        'lastname' => 'Smith',
        'firstname' => 'John',
        'address_type' => 'billing',
        'email' => 'some_email@mail.com',
        'region_id' => 1,
    ],
    [
        'telephone' => 123123,
        'postcode' => 'ZX0789',
        'country_id' => 'US',
        'city' => 'Ena4ka',
        'street' => ['Black', 'White'],
        'lastname' => 'Doe',
        'firstname' => 'John',
        'address_type' => 'billing',
        'email' => 'some_email@mail.com',
        'region_id' => 2,
    ],
    [
        'telephone' => 123123,
        'postcode' => 'ZX0789A',
        'country_id' => 'US',
        'city' => 'Ena4ka',
        'street' => ['Black', 'White'],
        'lastname' => 'Doe',
        'firstname' => 'John',
        'address_type' => 'shipping',
        'email' => 'some_email@mail.com',
        'region_id' => 1,
    ]
];

/** @var array $addresses */
foreach ($addresses as $addressData) {
    /** @var $address \Magento\Sales\Model\Order\Address */
    $address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Sales\Model\Order\Address::class
    );
    $address
        ->setData($addressData)
        ->save();
}
