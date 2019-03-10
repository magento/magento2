<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_with_tax.php';

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
        'region_id' => 12
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
    'guest'
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
