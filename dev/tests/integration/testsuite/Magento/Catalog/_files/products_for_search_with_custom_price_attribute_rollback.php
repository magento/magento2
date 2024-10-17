<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_for_search_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_price_attribute_rollback.php');
