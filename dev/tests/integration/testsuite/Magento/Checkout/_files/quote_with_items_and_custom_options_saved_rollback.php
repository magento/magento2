<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->removeSharedInstance(\Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile::class);

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_item_with_items_and_custom_options', 'reserved_order_id');
$quoteId = $quote->getId();
if ($quote->getId()) {
    $objectManager->get(\Magento\Quote\Model\QuoteRepository::class)->delete($quote);
}

require __DIR__ . '/../../Checkout/_files/quote_with_address_rollback.php';
