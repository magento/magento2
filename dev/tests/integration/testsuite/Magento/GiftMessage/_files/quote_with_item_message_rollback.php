<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create('Magento\Sales\Model\Quote');
$quote->load('test_order_item_with_message', 'reserved_order_id');
$message = $objectManager->create('Magento\GiftMessage\Model\Message');
$product = $objectManager->create('Magento\Catalog\Model\Product');
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
