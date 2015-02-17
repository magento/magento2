<?php
/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

// @codingStandardsIgnoreFile

namespace Magento\Core\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup->createMigrationSetup();
		$setup->startSetup();
		
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
		$tableName = $setup->getTable('authorization_rule');
		if ($tableName) {
		    $setup->getConnection()->delete($tableName, ['resource_id = ?' => 'admin/system/tools/compiler']);
		}
		
		$setup->endSetup();
		
	}
}