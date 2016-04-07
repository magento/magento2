<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_virtual.php';

/** @var \Magento\Quote\Model\Quote\Address $quoteShippingAddress */
$quoteShippingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Quote\Model\Quote\Address'
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

/** @var \Magento\Quote\Model\Quote $quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
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

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\QuoteIdMaskFactory')
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
