<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_item_with_message', 'reserved_order_id');
$message = $objectManager->create(\Magento\GiftMessage\Model\Message::class);
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
foreach ($quote->getAllItems() as $item) {
    $message->load($item->getGiftMessageId());
    $message->delete();
    $sku = $item->getSku();
    $product->load($product->getIdBySku($sku));
    if ($product->getId()) {
        $product->delete();
    }
};
$quote->delete();
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
