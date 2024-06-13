<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Eav/_files/empty_attribute_set_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_rollback.php');
