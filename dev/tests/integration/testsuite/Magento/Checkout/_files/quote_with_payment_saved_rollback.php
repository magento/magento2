<?php
/**
 * Rollback for quote_with_payment_saved.php fixture.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_1_with_payment', 'reserved_order_id')->delete();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMask::class);
$quoteIdMask->delete($quote->getId());
