<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/PriceCalculator/fixed_bundle_product_rollback.php');
Resolver::getInstance()->requireDataFixture(
    'Magento/CatalogRule/_files/catalog_rule_10_off_not_logged_rollback.php'
);
