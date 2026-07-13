<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/AdvancedPricingImportExport/_files/create_products_rollback.php');
