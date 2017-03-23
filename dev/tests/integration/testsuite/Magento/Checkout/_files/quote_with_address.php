<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Quote\Model\Quote\Address $quoteShippingAddress */
$quoteShippingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class);

/** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
$accountManagement = $objectManager->create(\Magento\Customer\Api\AccountManagementInterface::class);

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customer = $customerRepository->getById(1);

/** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->create(\Magento\Customer\Api\AddressRepositoryInterface::class);
$quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setStoreId(
    1
)->setIsActive(
    true
)->setIsMultiShipping(
    false
)->assignCustomerWithAddressChange(
    $customer
)->setShippingAddress(
    $quoteShippingAddress
)->setBillingAddress(
    $quoteShippingAddress
)->setCheckoutMethod(
    'customer'
)->setPasswordHash(
    $accountManagement->getPasswordHash('password')
)->setReservedOrderId(
    'test_order_1'
)->setCustomerEmail(
    'aaa@aaa.com'
)->addProduct(
    $product->load($product->getId()),
    2
);
