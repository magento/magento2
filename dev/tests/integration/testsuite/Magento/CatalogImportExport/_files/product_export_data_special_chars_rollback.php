<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** Delete all products */
include dirname(dirname(__DIR__)) . '/Catalog/_files/products_with_multiselect_attribute_rollback.php';
/** Delete text attribute */
include dirname(dirname(__DIR__)) . '/Catalog/_files/product_text_attribute_rollback.php';

include dirname(dirname(__DIR__)) . '/Store/_files/second_store_rollback.php';

include dirname(dirname(__DIR__)) . '/Catalog/_files/category_rollback.php';
