<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

try {
    require __DIR__ . '/../../../../Magento/Framework/Search/_files/products_rollback.php';
} catch (\Exception $e) {
    echo $e->getMessage();
}
