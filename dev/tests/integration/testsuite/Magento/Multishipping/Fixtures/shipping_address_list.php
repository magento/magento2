<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * @var Magento\Quote\Model\Quote $quote
 */

if (empty($quote)) {
    throw new \Exception('$quote should be defined in the parent fixture');
}

$addressList = [
    [
        'firstname' => 'Jonh',
        'lastname' => 'Doe',
        'telephone' => '0333-233-221',
        'street' => ['Main Division 1'],
        'city' => 'Culver City',
        'region' => 'CA',
        'postcode' => 90800,
        'country_id' => 'US',
        'email' => 'customer001@shipping.test',
        'address_type' => 'shipping',
    ],
    [
        'firstname' => 'Antoni',
        'lastname' => 'Holmes',
        'telephone' => '0333-233-221',
        'street' => ['Second Division 2'],
        'city' => 'Denver',
        'region' => 'CO',
        'postcode' => 80203,
        'country_id' => 'US',
        'email' => 'customer002@shipping.test',
        'address_type' => 'shipping'
    ]
];

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

$methodCode = 'flatrate_flatrate';
foreach ($addressList as $data) {
    /** @var Rate $rate */
    $rate = $objectManager->create(Rate::class);
    $rate->setCode($methodCode)
        ->setPrice(5.00);

    $address = $objectManager->create(AddressInterface::class, ['data' => $data]);
    $address->setShippingMethod($methodCode)
        ->addShippingRate($rate)
        ->setShippingAmount(5.00)
        ->setBaseShippingAmount(5.00);

    $quote->addAddress($address);
}
$quote->setIsMultiShipping(1);
