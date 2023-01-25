<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


/**
 * Cleanup
 */
$appDirs = ['app', 'pub/media', 'pub', 'var/log', 'var'];
foreach ($appDirs as $dir) {
    $appDir = TESTS_TEMP_DIR . '/Magento/Backup/data/' . $dir;
    if (is_dir($appDir)) {
        rmdir($appDir);
    }
}

$files = glob(TESTS_TEMP_DIR . '/Magento/Backup/data/*', GLOB_NOSORT);
foreach ($files as $file) {
    unlink($file);
}
rmdir(TESTS_TEMP_DIR . '/Magento/Backup/data');
rmdir(TESTS_TEMP_DIR . '/Magento/Backup');
