<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$quote->setStoreId(1)
    ->setIsActive(true)
    ->setIsMultiShipping(false)
    ->setReservedOrderId('test_order_1')
    ->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
