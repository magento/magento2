<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_address.php');

$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(
    'simple'
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product With Message'
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
$message = $objectManager->create(\Magento\GiftMessage\Model\Message::class);
$message->setSender('John Doe');
$message->setRecipient('Jane Roe');
$message->setMessage('Gift Message Text');
$message->save();
$quote->getItemByProduct($quoteProduct)->setGiftMessageId($message->getId())->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
