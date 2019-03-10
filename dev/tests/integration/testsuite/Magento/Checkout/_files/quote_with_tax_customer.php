<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

require __DIR__ . '/../../Customer/_files/customer_with_tax_group.php';
require __DIR__ . '/../../Customer/_files/customer_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_with_tax.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(AddressRepositoryInterface::class);
$customerAddress = $addressRepository->getById(1);
$customerAddress->setRegionId(12); // Taxable region
$addressRepository->save($customerAddress);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@example.com');

/** @var Address  $quoteAddress */
$quoteAddress = $objectManager->create(Address::class);
$quoteAddress->importCustomerAddressData($customerAddress);

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setStoreId(
    1
)->setIsActive(
    true
)->setIsMultiShipping(
    false
)->assignCustomer(
    $customer
)->setShippingAddress(
    $quoteAddress
)->setBillingAddress(
    $quoteAddress
)->setCheckoutMethod(
    'customer'
)->setReservedOrderId(
    'test_order_tax'
)->addProduct(
    $product
);

$quote->getShippingAddress()->setRegionId(12);

$quoteRepository = $objectManager->get(
    \Magento\Quote\Api\CartRepositoryInterface::class
);

$quoteRepository->save($quote);

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager
    ->get(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
