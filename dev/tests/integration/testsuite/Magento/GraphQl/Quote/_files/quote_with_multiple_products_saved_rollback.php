<?php
/**
 * Rollback for quote_with_payment_saved.php fixture.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_with_multiple_products_without_address', 'reserved_order_id')->delete();

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiple_products_rollback.php');
