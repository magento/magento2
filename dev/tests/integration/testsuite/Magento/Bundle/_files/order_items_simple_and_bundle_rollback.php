<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/product_with_multiple_options_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_product_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
