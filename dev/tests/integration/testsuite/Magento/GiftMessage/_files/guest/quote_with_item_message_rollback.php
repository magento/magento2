<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\GiftMessage\Model\Message;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$objectManager = Bootstrap::getObjectManager();
$quote = $objectManager->create(Quote::class);
$quote->load('test_guest_order_with_gift_message', 'reserved_order_id');
$message = $objectManager->create(Message::class);
$product = $objectManager->create(Product::class);
foreach ($quote->getAllItems() as $item) {
    $message->load($item->getGiftMessageId());
    $message->delete();
    $sku = $item->getSku();
    $product->load($product->getIdBySku($sku));
    if ($product->getId()) {
        $product->delete();
    }
}
$quote->delete();
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
