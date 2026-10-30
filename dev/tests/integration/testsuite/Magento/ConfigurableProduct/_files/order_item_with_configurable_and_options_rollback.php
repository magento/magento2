<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
