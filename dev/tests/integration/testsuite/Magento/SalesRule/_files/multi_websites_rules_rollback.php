<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/website_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/rules_rollback.php');
