<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/product_downloadable.php';

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('frontend');
$product->load(1);

/** @var \Magento\Quote\Model\Quote $quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
$quote->setCustomerIsGuest(
    true
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Store\Model\StoreManagerInterface'
    )->getStore()->getId()
)->setReservedOrderId(
    'reserved_order_id_1'
)->setIsMultiShipping(
    false
)->addProduct(
    $product,
    new \Magento\Framework\DataObject([
        'links' => array_keys($product->getDownloadableLinks())
    ])
);
$quote->collectTotals();
$quote->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\QuoteIdMaskFactory')
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
