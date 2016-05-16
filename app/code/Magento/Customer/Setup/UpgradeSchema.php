<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

        $setup->endSetup();
    }
}
