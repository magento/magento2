<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


/**
 * Create directories structure as in application
 */
$appDirs = ['app', 'pub/media', 'var/log'];
foreach ($appDirs as $dir) {
    $appDir = TESTS_TEMP_DIR . '/Magento/Backup/data/' . $dir;
    if (!is_dir($appDir)) {
        mkdir($appDir, 0777, true);
    }
}
