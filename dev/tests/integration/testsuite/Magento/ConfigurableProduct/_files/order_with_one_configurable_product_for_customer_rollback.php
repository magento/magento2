<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/CatalogRule/_files/configurable_product_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/GraphQl/Quote/_files/customer/create_empty_cart_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
