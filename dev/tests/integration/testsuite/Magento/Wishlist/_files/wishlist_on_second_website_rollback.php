<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_with_websites_and_stores_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_non_default_website_id_rollback.php');
