<?php
/**
 * Rollback for quote_with_virtual_product_and_address.php fixture.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create('Magento\Quote\Model\Quote');
$quote->load('test_order_with_virtual_product', 'reserved_order_id')->delete();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create('Magento\Quote\Model\QuoteIdMask');
$quoteIdMask->delete($quote->getId());
