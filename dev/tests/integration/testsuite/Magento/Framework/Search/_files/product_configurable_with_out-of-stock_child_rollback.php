<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Framework/Search/_files/product_configurable_rollback.php');
