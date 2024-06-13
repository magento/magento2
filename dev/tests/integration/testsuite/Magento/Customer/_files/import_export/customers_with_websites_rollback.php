<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/import_export/customers_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website_rollback.php');
