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

$omFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, []);
$objectManager = $omFactory->create(
    [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
);

$templates = (new Magento\Framework\Test\Utility\Files(BP))->getPhpFiles(false, false, true, false);
foreach ($templates as $template) {
    $objectManager->get('Magento\Framework\View\Template\Html\MinifierInterface')->minify($template);
}
