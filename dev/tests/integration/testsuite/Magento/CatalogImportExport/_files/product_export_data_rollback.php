<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_with_multiselect_attribute_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_text_attribute_rollback.php');
