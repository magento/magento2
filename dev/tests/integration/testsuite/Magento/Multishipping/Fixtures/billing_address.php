<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Api\Data\AddressInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @var Magento\Quote\Model\Quote $quote
 */

if (empty($quote)) {
    throw new \Exception('$quote should be defined in the parent fixture');
}

$data = [
    'firstname' => 'Jonh',
    'lastname' => 'Doe',
    'telephone' => '0333-233-221',
    'street' => ['Third Division 1'],
    'city' => 'New York',
    'region' => 'NY',
    'postcode' => 10029,
    'country_id' => 'US',
    'email' => 'customer001@billing.test',
    'address_type' => 'billing',
];

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var AddressInterface $address */
$address = $objectManager->create(AddressInterface::class, ['data' => $data]);
$quote->setBillingAddress($address);
