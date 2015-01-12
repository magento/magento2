<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

/** @var $quote \Magento\Sales\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
$quote->setStoreId(1)->setIsActive(false)->setIsMultiShipping(false)->addProduct($product->load($product->getId()), 2);

$quote->getPayment()->setMethod('checkmo');

$quote->collectTotals();
$quote->save();

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quoteService = $objectManager->create('Magento\Sales\Model\Service\Quote', ['quote' => $quote]);
$quoteService->getQuote()->getPayment()->setMethod('checkmo');
