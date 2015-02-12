<?php
/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

// @codingStandardsIgnoreFile

namespace Magento\DesignEditor\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleSchemaResourceInterface;

class InstallSchema implements InstallSchemaInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function install(ModuleSchemaResourceInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		
		$installer->startSetup();
		$connection = $installer->getConnection();
		
		/**
		 * Create table 'vde_theme_change'
		 */
		$table = $installer->getConnection()->newTable(
		    $installer->getTable('vde_theme_change')
		)->addColumn(
		    'change_id',
		    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
		    null,
		    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
		    'Theme Change Identifier'
		)->addColumn(
		    'theme_id',
		    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
		    null,
		    ['nullable' => false, 'unsigned' => true],
		    'Theme Id'
		)->addColumn(
		    'change_time',
		    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
		    null,
		    ['nullable' => false],
		    'Change Time'
		)->addForeignKey(
		    $installer->getFkName('vde_theme_change', 'theme_id', 'theme', 'theme_id'),
		    'theme_id',
		    $installer->getTable('theme'),
		    'theme_id',
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->setComment(
		    'Design Editor Theme Change'
		);
		
		$installer->getConnection()->createTable($table);
		
		$installer->endSetup();
		
	}
}