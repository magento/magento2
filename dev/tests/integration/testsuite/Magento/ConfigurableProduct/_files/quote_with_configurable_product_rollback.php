<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create('Magento\Quote\Model\Quote');
$quote->load('test_cart_with_configurable', 'reserved_order_id');
$quote->delete();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create('Magento\Quote\Model\QuoteIdMask');
$quoteIdMask->delete($quote->getId());

require __DIR__ . '/product_configurable_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
