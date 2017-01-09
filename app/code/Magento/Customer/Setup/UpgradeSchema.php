<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0.1') < 0) {
            $connection = $setup->getConnection();

            $connection->addIndex(
                $setup->getTable('customer_visitor'),
                $setup->getIdxName('customer_visitor', ['last_visit_at']),
                ['last_visit_at']
            );
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_eav_attribute'),
                'is_used_in_grid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Used in Grid'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_eav_attribute'),
                'is_visible_in_grid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Visible in Grid'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_eav_attribute'),
                'is_filterable_in_grid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Filterable in Grid'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_eav_attribute'),
                'is_searchable_in_grid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Searchable in Grid'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'failures_num',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Failure Number'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'first_failure',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'comment' => 'First Failure'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'lock_expires',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'comment' => 'Lock Expiration Date'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.10', '<')) {
            $foreignKeys = $this->getForeignKeys($setup);
            $this->dropForeignKeys($setup, $foreignKeys);
            $this->alterTables($setup, $foreignKeys);
            $this->createForeignKeys($setup, $foreignKeys);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array $keys
     * @return void
     */
    private function alterTables(SchemaSetupInterface $setup, array $keys)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable('customer_group'),
            'customer_group_id',
            [
                'type' => 'integer',
                'unsigned' => true,
                'identity' => true,
                'nullable' => false
            ]
        );
        foreach ($keys as $key) {
            $description = $setup->getConnection()->describeTable($key['TABLE_NAME'])[$key['COLUMN_NAME']];
            $description['DATA_TYPE'] = 'int';
            $setup->getConnection()->modifyColumnByDdl(
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $description
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array $keys
     * @return void
     */
    private function dropForeignKeys(SchemaSetupInterface $setup, array $keys)
    {
        foreach ($keys as $key) {
            $setup->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array $keys
     * @return void
     */
    private function createForeignKeys(SchemaSetupInterface $setup, array $keys)
    {
        foreach ($keys as $key) {
            $setup->getConnection()->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return array
     */
    private function getForeignKeys(SchemaSetupInterface $setup)
    {
        $foreignKeys = [];
        $keysTree = $setup->getConnection()->getForeignKeysTree();
        foreach ($keysTree as $indexes) {
            foreach ($indexes as $index) {
                if (
                    $index['REF_TABLE_NAME'] == $setup->getTable('customer_group')
                    && $index['REF_COLUMN_NAME'] == 'customer_group_id'
                ) {
                    $foreignKeys[] = $index;
                }

            }
        }
        return $foreignKeys;
    }
}
