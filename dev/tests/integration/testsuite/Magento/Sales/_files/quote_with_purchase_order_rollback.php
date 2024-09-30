<?php
/**
 * Rollback for quote_with_purchase_order.php fixture.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = Bootstrap::getObjectManager();
$objectManager->get(Registry::class)->unregister('quote');
$quote = $objectManager->create(Quote::class);
$quote->load('test_order_1', 'reserved_order_id')->delete();
