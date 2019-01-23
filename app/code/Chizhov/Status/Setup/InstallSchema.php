<?php

declare(strict_types=1);

namespace Chizhov\Status\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createCustomerStatusTable($setup);

        $setup->endSetup();
    }

    /**
     * Create the chizhov_customer_status table.
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    private function createCustomerStatusTable(SchemaSetupInterface $setup): void
    {
        $tableName = $setup->getTable('chizhov_customer_status');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['primary' => true, 'nullable' => false, 'unsigned' => true],
                'Customer ID'
            )->addColumn(
                'customer_status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Customer Status'
            )->addForeignKey(
                $setup->getFkName(
                    $tableName,
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                'customer_entity',
                'entity_id',
                Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }
}
