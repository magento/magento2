<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_2', 'reserved_order_id')
    ->delete();

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores_rollback.php');
