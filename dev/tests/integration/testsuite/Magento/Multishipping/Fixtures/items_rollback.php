<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/simple_product_10_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/simple_product_20_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Multishipping/Fixtures/virtual_product_5_rollback.php');
