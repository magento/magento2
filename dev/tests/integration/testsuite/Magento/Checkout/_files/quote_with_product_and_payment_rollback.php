<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/rollback_quote.php');
