<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/coupons_limited_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_rollback.php');
