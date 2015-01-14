<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Theme\Model\Resource\Setup */
$installer = $this->createMigrationSetup();
$installer->startSetup();

/**
 * Update theme's data
 */
$fileCollection = $this->createThemeFactory();
$fileCollection->addDefaultPattern('*');
$fileCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

$resourceCollection = $this->createThemeResourceFactory();
$resourceCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
foreach ($resourceCollection as $theme) {
    $themeType = $fileCollection->hasTheme($theme)
        ? \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
        : \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL;
    $theme->setType($themeType)->save();
}

$fileCollection = $this->createThemeFactory();
$fileCollection->addDefaultPattern('*');
$fileCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

$themeDbCollection = $this->createThemeResourceFactory();
$themeDbCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
foreach ($fileCollection as $theme) {
    $dbTheme = $themeDbCollection->getThemeByFullPath($theme->getFullPath());
    $dbTheme->setCode($theme->getCode());
    $dbTheme->save();
}

/**
 * Update rows in theme
 */
$installer->getConnection()->update(
    $installer->getTable('theme'),
    ['area' => 'frontend'],
    ['area = ?' => '']
);
$installer->getEventManager()->dispatch('theme_registration_from_filesystem');

$installer->endSetup();
