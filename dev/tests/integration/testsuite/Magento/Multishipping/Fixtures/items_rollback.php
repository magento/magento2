<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/simple_product_10_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/simple_product_20_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/virtual_product_5_rollback.php');
