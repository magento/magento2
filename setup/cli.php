<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

try {
    require __DIR__ . '/../app/bootstrap.php';
    $application = new Magento\Setup\Console\Application('Magento CLI');
    $application->run();
} catch (\Exception $e) {
    if (PHP_SAPI == 'cli') {
        echo 'Autoload error: ' . $e->getMessage();
    }
    exit(1);
}
