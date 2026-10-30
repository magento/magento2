<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/product_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/bundle_product_dropdown_options_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
