<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_virtual.php';

/** @var \Magento\Sales\Model\Quote\Address $quoteShippingAddress */
$quoteShippingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Sales\Model\Quote\Address'
);
/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Api\CustomerRepositoryInterface'
);
/** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
$addressRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Api\AddressRepositoryInterface'
);
$quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));

/** @var \Magento\Sales\Model\Quote $quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
$quote->setStoreId(
        1
    )->setIsActive(
        true
    )->setIsMultiShipping(
        false
    )->assignCustomerWithAddressChange(
        $customerRepository->getById($customer->getId())
    )->setShippingAddress(
        $quoteShippingAddress
    )->setBillingAddress(
        $quoteShippingAddress
    )->setCheckoutMethod(
        $customer->getMode()
    )->setPasswordHash(
        $customer->encryptPassword($customer->getPassword())
    )->setReservedOrderId(
        'test_order_with_virtual_product'
    )->setEmail(
        'store@example.com'
    )->addProduct(
        $product->load($product->getId()),
        1
    );

$quote->collectTotals()->save();
