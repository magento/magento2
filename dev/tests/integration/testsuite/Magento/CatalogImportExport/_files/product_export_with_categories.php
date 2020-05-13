<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/categories.php');
