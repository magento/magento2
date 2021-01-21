<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/AdvancedPricingImportExport/_files/create_products_rollback.php');
