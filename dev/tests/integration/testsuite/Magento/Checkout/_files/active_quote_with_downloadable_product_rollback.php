<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Downloadable/_files/product_downloadable_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/active_quote_rollback.php');
