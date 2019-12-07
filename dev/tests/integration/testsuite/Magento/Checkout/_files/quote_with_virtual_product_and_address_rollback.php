<?php
/**
 * Rollback for quote_with_virtual_product_and_address.php fixture.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_with_virtual_product', 'reserved_order_id')->delete();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);
$quoteIdMask->delete($quote->getId());

require __DIR__ . '/../../Customer/_files/customer_rollback.php';
require __DIR__ . '/../../Customer/_files/customer_address_rollback.php';
require __DIR__ . '/../../Catalog/_files/product_virtual_rollback.php';
