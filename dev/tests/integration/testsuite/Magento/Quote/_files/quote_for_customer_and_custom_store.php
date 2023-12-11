<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

$customer = $objectManager->get(CustomerRepositoryInterface::class)->get('customer@example.com');
$store = $objectManager->get(StoreRepositoryInterface::class)->get('secondstore');

$addressData = include __DIR__ . '/../../Customer/Fixtures/address_data.php';
/** @var Address $shippingAddress */
$shippingAddress = $objectManager->create(Address::class, ['data' => $addressData[0]]);
$shippingAddress->setAddressType('shipping');

$billingAddress = clone $shippingAddress;
$billingAddress->setId(null)
    ->setAddressType('billing');

/** @var Quote $quote */
$quote = $objectManager->create(
    Quote::class,
    [
        'data' => [
            'customer_id' => $customer->getId(),
            'store_id' => $store->getId(),
            'reserved_order_id' => 'tsg-123456789',
            'is_active' => true,
            'is_multishipping' => false
        ],
    ]
);
$quote->setShippingAddress($shippingAddress)
    ->setBillingAddress($billingAddress);

/** @var CartRepositoryInterface $repository */
$repository = $objectManager->get(CartRepositoryInterface::class);
$repository->save($quote);
