<?php
/**
 * Rollback for quote_with_check_payment.php fixture.
 *
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get(\Magento\Framework\Registry::class)->unregister('quote');
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_1', 'reserved_order_id')->delete();
