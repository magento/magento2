<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/GroupedProduct/_files/product_grouped_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_virtual_out_of_stock_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_virtual_in_stock_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_associated_rollback.php');
