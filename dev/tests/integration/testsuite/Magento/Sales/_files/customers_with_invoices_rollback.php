<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/three_customers_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
