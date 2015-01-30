<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Autoload\AutoloaderRegistry;

$baseName = basename(__FILE__);
require __DIR__ . '/../../../../../app/bootstrap.php';

AutoloaderRegistry::getAutoloader()->addPsr4(
    'Magento\\',
    [realpath(__DIR__ . '/../../../Magento/')]
);

$templates = (new Magento\Framework\Test\Utility\Files(BP))->getPhpFiles(false, false, true, false);
foreach ($templates as $template) {
    $minifier = new \Magento\Framework\View\Template\Html\Minifier(
            new Magento\Framework\Filesystem\Driver\File()
        );
    $minifier->minify($template);
}
