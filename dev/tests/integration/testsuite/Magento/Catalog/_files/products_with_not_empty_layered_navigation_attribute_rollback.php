<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/products_with_layered_navigation_attribute_rollback.php'
);
