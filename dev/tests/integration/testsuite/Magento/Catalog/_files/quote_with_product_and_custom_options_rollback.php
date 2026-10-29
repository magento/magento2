<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/active_quote_rollback.php');
