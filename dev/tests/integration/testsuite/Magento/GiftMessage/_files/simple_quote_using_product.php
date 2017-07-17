<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\Product::class
);
$product->load(1);

/** @var $quote \Magento\Quote\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->getStore()->getId();

$quoteItem = $quote->setStoreId($storeId)
    ->setReservedOrderId('test01')
    ->addProduct($product, 10);
/** @var $quoteItem \Magento\Quote\Model\Quote\Item */
$quoteItem->setQty(1);
$quote->getPayment()->setMethod('checkmo');
$quote->getBillingAddress();
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->collectTotals();
$quote->save();
$quoteItem->setQuote($quote);
$quoteItem->save();
