<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_address_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_rollback.php');
