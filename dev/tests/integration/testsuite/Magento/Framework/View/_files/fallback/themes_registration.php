<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize([
    Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
        DirectoryList::THEMES => ['path' => __DIR__ . '/design'],
    ],
]);
$objectManger = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $registration \Magento\Core\Model\Theme\Registration */
$registration = $objectManger->create('Magento\Core\Model\Theme\Registration');
$registration->register('*/*/*/theme.xml');
