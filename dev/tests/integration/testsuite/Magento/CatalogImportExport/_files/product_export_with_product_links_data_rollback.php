<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** Remove fixture category */
require dirname(dirname(__DIR__)) . '/Catalog/_files/category_rollback.php';
/** Remove fixture store */
require dirname(dirname(__DIR__)) . '/Store/_files/second_store_rollback.php';
/** Delete all products */
require dirname(dirname(__DIR__)) . '/Catalog/_files/products_with_multiselect_attribute_rollback.php';
