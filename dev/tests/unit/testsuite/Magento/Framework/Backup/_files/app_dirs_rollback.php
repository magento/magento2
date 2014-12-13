<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

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

$files = glob(TESTS_TEMP_DIR . '/Magento/Backup/data/*');
foreach ($files as $file) {
    unlink($file);
}
rmdir(TESTS_TEMP_DIR . '/Magento/Backup/data');
rmdir(TESTS_TEMP_DIR . '/Magento/Backup');
