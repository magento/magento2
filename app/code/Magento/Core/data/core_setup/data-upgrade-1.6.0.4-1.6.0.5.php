<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $installer \Magento\Core\Model\Resource\Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('authorization_rule');
if ($tableName) {
    $installer->getConnection()->delete($tableName, ['resource_id = ?' => 'admin/system/tools/compiler']);
}
$tableName = $installer->getTable('core_resource');
if ($tableName) {
    $installer->getConnection()->delete($tableName, ['code = ?' => 'admin_setup']);
}

$installer->endSetup();
