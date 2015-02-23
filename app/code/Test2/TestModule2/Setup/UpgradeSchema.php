<?php
namespace Test\TestModule1\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        echo $context->getVersion();
        if (version_compare($context->getVersion(), '2.0.0.1') < 0) {
            $setup->startSetup();
            /**
             * Create table 'test_table2'
             */
            $table = $setup->getConnection()->newTable(
                $setup->getTable('test_table2')
            )->addColumn(
                    'column21',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Table 2 Column 1'
                )->addColumn(
                    'column22',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Table 2 Column 2'
                )->addColumn(
                    'column23',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Table 2 Column3'
                )->addIndex(
                    $setup->getIdxName('test_table2', ['column21']),
                    ['column21']
                )->addForeignKey(
                    $setup->getFkName('test_table2', 'column21', 'test_table1', 'column11'),
                    'column21',
                    $setup->getTable('test_table1'),
                    'column11',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
            $setup->getConnection()->createTable($table);

            $setup->endSetup();
        }
    }
}
