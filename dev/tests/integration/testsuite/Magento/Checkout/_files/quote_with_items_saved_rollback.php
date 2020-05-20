<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_item_with_items', 'reserved_order_id');
$quoteId = $quote->getId();
if ($quote->getId()) {
    $quote->delete();
}
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->load($product->getIdBySku('simple_one'));
if ($product->getId()) {
    $product->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_address_rollback.php');
