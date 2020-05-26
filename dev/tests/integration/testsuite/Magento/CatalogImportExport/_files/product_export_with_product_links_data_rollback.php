<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** Remove fixture category */
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_rollback.php');
/** Remove fixture store */
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store_rollback.php');
/** Delete all products */
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_with_multiselect_attribute_rollback.php');
