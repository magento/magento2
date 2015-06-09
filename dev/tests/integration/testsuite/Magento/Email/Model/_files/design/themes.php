<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$objectManager->get('Magento\Framework\App\AreaList')
    ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
    ->load(\Magento\Framework\App\Area::PART_CONFIG);
\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize([
    Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
        DirectoryList::THEMES => ['path' => realpath(__DIR__)],
    ],
]);
$objectManager->configure(
    ['preferences' => ['Magento\Theme\Model\Theme' => 'Magento\Theme\Model\Theme\Data']]
);
/** @var $registration \Magento\Theme\Model\Theme\Registration */
$registration = $objectManager->create(
    'Magento\Theme\Model\Theme\Registration'
);

// It is not possible to set custom admin theme via store config, as the default adminhtml theme is set in
// app/code/Magento/Theme/etc/di.xml. To modify the adminhtml theme, we must change the injected "theme" argument here.
$objectManager->configure([
    'Magento\Theme\Model\View\Design' => [
        'arguments' => [
            'themes' => [
                'frontend' => 'Magento/blank',
                'adminhtml' => 'Vendor/custom_theme',
            ],
        ]
    ],
]);

$registration->register(implode('/', ['*', '*', '*', 'theme.xml']));
