<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** Delete all products */
include dirname(dirname(__DIR__)) . '/Catalog/_files/products_with_multiselect_attribute_rollback.php';
/** Delete text attribute */
<<<<<<< HEAD
require dirname(dirname(__DIR__)) . '/Catalog/_files/text_attribute_rollback.php';

require dirname(dirname(__DIR__)) . '/Store/_files/second_store_rollback.php';

require dirname(dirname(__DIR__)) . '/Catalog/_files/category_rollback.php';
=======
include dirname(dirname(__DIR__)) . '/Catalog/_files/product_text_attribute_rollback.php';

include dirname(dirname(__DIR__)) . '/Store/_files/second_store_rollback.php';

include dirname(dirname(__DIR__)) . '/Catalog/_files/category_rollback.php';
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
