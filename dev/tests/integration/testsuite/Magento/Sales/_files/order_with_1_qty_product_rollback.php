<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_two_simple_products_qty_10_rollback.php');
