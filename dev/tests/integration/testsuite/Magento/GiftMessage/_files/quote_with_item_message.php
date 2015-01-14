<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Checkout/_files/quote_with_address.php';
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    'simple'
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product'
)->setSku(
    'simple_with_message'
)->setPrice(
    10
)->setMetaTitle(
    'meta title'
)->setMetaKeyword(
    'meta keyword'
)->setMetaDescription(
    'meta description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setStockData(
    ['use_config_manage_stock' => 0]
)->save();
$quoteProduct = $product->load($product->getIdBySku('simple_with_message'));
$quote->setReservedOrderId('test_order_item_with_message')
    ->addProduct($product->load($product->getIdBySku('simple_with_message')), 1);
$quote->collectTotals()->save();

/** @var \Magento\GiftMessage\Model\Message $message */
$message = $objectManager->create('Magento\GiftMessage\Model\Message');
$message->setSender('John Doe');
$message->setRecipient('Jane Roe');
$message->setMessage('Gift Message Text');
$message->save();
$quote->getItemByProduct($quoteProduct)->setGiftMessageId($message->getId())->save();
