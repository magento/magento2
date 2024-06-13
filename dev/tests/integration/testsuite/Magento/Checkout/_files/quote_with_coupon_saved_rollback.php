<?php
/**
 * Rollback for quote_with_coupon_saved.php fixture.
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_1', 'reserved_order_id')->delete();
