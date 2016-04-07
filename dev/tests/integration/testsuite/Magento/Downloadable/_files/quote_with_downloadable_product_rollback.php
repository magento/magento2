<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create('Magento\Quote\Model\Quote');
$quote->load('reserved_order_id_1', 'reserved_order_id')->delete();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create('Magento\Quote\Model\QuoteIdMask');
$quoteIdMask->delete($quote->getId());
require __DIR__ . '/product_downloadable_rollback.php';
