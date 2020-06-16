<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/category_with_different_price_products_rollback.php'
);
Resolver::getInstance()->requireDataFixture(
    'Magento/Store/_files/second_website_with_two_stores_rollback.php'
);
