<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_for_search_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_price_attribute_rollback.php');
