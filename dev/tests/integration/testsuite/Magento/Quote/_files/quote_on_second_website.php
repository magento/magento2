<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Catalog/_files/products_with_websites_and_stores.php';

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(Magento\Framework\App\Area::AREA_FRONTEND);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$addressData = include __DIR__ . '/../../Sales/_files/address_data.php';
$billingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product2 = $productRepository->get('simple-2');

/** @var \Magento\Store\Model\Store $store */
$store = $objectManager->create(\Magento\Store\Model\Store::class);
$store->load('fixture_second_store', 'code');

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setCustomerIsGuest(true)
    ->setStoreId($store->getId())
    ->setReservedOrderId('test01')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->addProduct($product2);
$quote->getPayment()->setMethod('checkmo');
$quote->setIsMultiShipping('1');
$quote->collectTotals();
$quote->save();
