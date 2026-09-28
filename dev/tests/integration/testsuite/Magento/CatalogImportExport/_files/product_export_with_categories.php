<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/categories.php');
