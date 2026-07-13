<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_attribute_first_rollback.php'
);
