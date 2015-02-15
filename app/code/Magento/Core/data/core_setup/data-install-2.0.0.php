<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Framework\Module\DataSetup */
$installer = $this->createMigrationSetup();
$installer->startSetup();

$installer->appendClassAliasReplace(
    'core_config_data',
    'value',
    \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
    \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
    ['config_id']
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

$installer->endSetup();
