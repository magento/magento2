<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_product_with_one_simple_with_category.php');
