<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../../Magento/Catalog/_files/products.php';

use Magento\Framework\ObjectManagerInterface;
use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\ResourceModel\Message as MessageResource;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResource;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMaskFactory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var QuoteResource $quote */
$quote = $objectManager->create(QuoteResource::class);

/** @var Quote $quoteModel */
$quoteModel = $objectManager->create(Quote::class);
$quoteModel->setData(['store_id' => 1, 'is_active' => 1, 'is_multi_shipping' => 0]);
$quote->save($quoteModel);

$quoteProduct = $product->load($product->getIdBySku('simple'));

$quoteModel->setReservedOrderId('test_guest_order_with_gift_message')
    ->addProduct($product->load($product->getIdBySku('simple')), 1);
$quoteModel->collectTotals();
$quote->save($quoteModel);

/** @var MessageResource $message */
$message = $objectManager->create(MessageResource::class);

/** @var Message $message */
$messageModel = $objectManager->create(Message::class);

$messageModel->setSender('John Doe');
$messageModel->setRecipient('Jane Roe');
$messageModel->setMessage('Gift Message Text');
$message->save($messageModel);

$quoteModel->getItemByProduct($quoteProduct)->setGiftMessageId($messageModel->getId());
$quote->save($quoteModel);

/** @var QuoteIdMaskResource $quoteIdMask */
$quoteIdMask = Bootstrap::getObjectManager()
    ->create(QuoteIdMaskFactory::class)
    ->create();

/** @var QuoteIdMask $quoteIdMaskModel */
$quoteIdMaskModel = $objectManager->create(QuoteIdMask::class);

$quoteIdMaskModel->setQuoteId($quoteModel->getId());
$quoteIdMaskModel->setDataChanges(true);
$quoteIdMask->save($quoteIdMaskModel);
