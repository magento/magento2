<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestSetupDeclarationModule7\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * UpgradeSchema mock class
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $installer
                ->getConnection()
                ->modifyColumn('test_table', 'float', ['type' => 'float', 'default' => 29]);
        }
        //Create table and check, that Magento can`t delete it
        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('custom_table')
            )->addColumn(
                'custom_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Custom Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Custom Name'
            )->setComment(
                'Custom Table'
            );
            $setup->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
