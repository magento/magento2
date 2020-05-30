<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_product_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/second_product_simple_rollback.php');
