<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

require 'simple_product.php';

/** @var \Magento\Quote\Model\Quote $quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
$quote->setStoreId(
    1
    )->setIsActive(
        true
    )->setIsMultiShipping(
        false
    )->setReservedOrderId(
        'test_order_with_simple_product_without_address'
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
