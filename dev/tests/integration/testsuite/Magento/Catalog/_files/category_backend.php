<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');
