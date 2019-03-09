<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;

require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var AddressInterface $quoteAddress */
$quoteAddress = $objectManager->create(AddressInterface::class);
$quoteAddress->setData(
    [
        'telephone' => 3468676,
        'postcode' => 75477,
        'country_id' => 'US',
        'city' => 'CityM',
        'company' => 'CompanyName',
        'street' => 'Green str, 67',
        'lastname' => 'Smith',
        'firstname' => 'John',
        'region_id' => 1
    ]
);

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setStoreId(
    1
)->setIsActive(
    true
)->setIsMultiShipping(
    false
)->setShippingAddress(
    $quoteAddress
)->setBillingAddress(
    $quoteAddress
)->setCheckoutMethod(
    'customer'
)->setReservedOrderId(
    'test_order_1'
)->addProduct(
    $product
);

$quoteRepository = $objectManager->get(
    \Magento\Quote\Api\CartRepositoryInterface::class
);

$quoteRepository->save($quote);

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
