<?php
/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

// @codingStandardsIgnoreFile

namespace Magento\Core\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataResourceInterface;

class InstallData implements InstallDataInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function install(ModuleDataResourceInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup->createMigrationSetup();
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
		
		$installer->endSetup();
		
	}
}