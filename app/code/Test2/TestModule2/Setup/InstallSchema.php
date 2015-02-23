<?php
namespace Test\TestModule1\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Create table 'test_table1'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('test_table1')
        )->addColumn(
            'column11',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Table 1 Column 1'
        )->addColumn(
            'column12',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Table 1 Column 2'
        )->addColumn(
            'column13',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Table 1 Column 3'
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
