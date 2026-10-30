<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

\Magento\TestFramework\Helper\Bootstrap::getInstance()
    ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');
