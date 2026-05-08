<?php
/**
 * Rollback for quote_with_configurable_product_last_variation.php fixture.
 *
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_with_configurable_product', 'reserved_order_id')->delete();
