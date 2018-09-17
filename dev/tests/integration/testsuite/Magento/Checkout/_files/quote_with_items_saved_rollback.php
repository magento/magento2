<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$quote = $objectManager->create('Magento\Quote\Model\Quote');
$quote->load('test_order_item_with_items', 'reserved_order_id');
$quoteId = $quote->getId();
if ($quote->getId()) {
    $quote->delete();
}
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->load($product->getIdBySku('simple_one'));
if ($product->getId()) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../Checkout/_files/quote_with_address_rollback.php';
