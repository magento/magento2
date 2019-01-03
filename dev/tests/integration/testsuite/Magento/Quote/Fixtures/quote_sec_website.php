<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Api\Data\StoreInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @var CustomerInterface $customer
 * @var StoreInterface $store
 * @var ProductInterface $product
 */
require __DIR__ . '/../../Customer/Fixtures/customer_sec_website.php';
require __DIR__ . '/simple_product.php';

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

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
            'reserved_order_id' => '0000032134',
            'is_active' => true,
            'is_multishipping' => false
        ]
    ]
);
$quote->setShippingAddress($shippingAddress)
    ->setBillingAddress($billingAddress)
    ->addProduct($product);

$quote->getPayment()
    ->setMethod('checkmo');
$quote->getShippingAddress()
    ->setShippingMethod('flatrate_flatrate')
    ->setCollectShippingRates(true);
$quote->collectTotals();

/** @var CartRepositoryInterface $repository */
$repository = $objectManager->get(CartRepositoryInterface::class);
$repository->save($quote);
