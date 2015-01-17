<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Quote\Address $quoteShippingAddress */
$quoteShippingAddress = $objectManager->create('Magento\Sales\Model\Quote\Address');

/** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
$accountManagement = $objectManager->create('Magento\Customer\Api\AccountManagementInterface');

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
$customer = $customerRepository->getById(1);

/** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->create('Magento\Customer\Api\AddressRepositoryInterface');
$quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));

/** @var \Magento\Sales\Model\Quote $quote */
$quote = $objectManager->create('Magento\Sales\Model\Quote');
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
