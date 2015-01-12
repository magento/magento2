<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Core\Model\Resource\Setup */
$installer = $this->createMigrationSetup();
$installer->startSetup();

$installer->appendClassAliasReplace(
    'core_config_data',
    'value',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['config_id']
);
$installer->appendClassAliasReplace(
    'core_layout_update',
    'xml',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
    ['layout_update_id']
);
$installer->doUpdateClassAliases();

/**
 * Delete rows by condition from authorization_rule
 */
$tableName = $installer->getTable('authorization_rule');
if ($tableName) {
    $installer->getConnection()->delete($tableName, ['resource_id = ?' => 'admin/system/tools/compiler']);
}

/**
 * Delete rows by condition from core_resource
 */
$tableName = $installer->getTable('core_resource');
if ($tableName) {
    $installer->getConnection()->delete($tableName, ['code = ?' => 'admin_setup']);
}

/**
 * Update rows in core_theme
 */
$installer->getConnection()->update(
    $installer->getTable('core_theme'),
    ['area' => 'frontend'],
    ['area = ?' => '']
);
$installer->getEventManager()->dispatch('theme_registration_from_filesystem');

/**
 * Update theme's data
 */
$fileCollection = $this->createThemeFactory();
$fileCollection->addDefaultPattern('*');
$fileCollection->setItemObjectClass('Magento\Core\Model\Theme\Data');

$resourceCollection = $this->createThemeResourceFactory();
$resourceCollection->setItemObjectClass('Magento\Core\Model\Theme\Data');

/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
foreach ($resourceCollection as $theme) {
    $themeType = $fileCollection->hasTheme($theme)
        ? \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
        : \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL;
    $theme->setType($themeType)->save();
}

$fileCollection = $this->createThemeFactory();
$fileCollection->addDefaultPattern('*');
$fileCollection->setItemObjectClass('Magento\Core\Model\Theme\Data');

$themeDbCollection = $this->createThemeResourceFactory();
$themeDbCollection->setItemObjectClass('Magento\Core\Model\Theme\Data');

/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
foreach ($fileCollection as $theme) {
    $dbTheme = $themeDbCollection->getThemeByFullPath($theme->getFullPath());
    $dbTheme->setCode($theme->getCode());
    $dbTheme->save();
}

$installer->endSetup();
