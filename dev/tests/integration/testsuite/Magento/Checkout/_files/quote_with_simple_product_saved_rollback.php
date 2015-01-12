<?php
/**
 * Rollback for quote_with_payment_saved.php fixture.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create('Magento\Sales\Model\Quote');
$quote->load('test_order_with_simple_product_without_address', 'reserved_order_id')->delete();
