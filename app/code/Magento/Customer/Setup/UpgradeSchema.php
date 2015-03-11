<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0.1') < 0) {
            $installer = $setup;
            $connection = $installer->getConnection();

            $tableNames = [
                'customer_address_entity_varchar', 'customer_address_entity_datetime',
                'customer_address_entity_decimal', 'customer_address_entity_int', 'customer_address_entity_text',
                'customer_entity_varchar', 'customer_entity_datetime',
                'customer_entity_decimal', 'customer_entity_int', 'customer_entity_text'
            ];

            foreach ($tableNames as $table) {
                $connection->dropForeignKey(
                    $installer->getTable($table),
                    $installer->getFkName($table, 'entity_type_id', 'eav_entity_type', 'entity_type_id')
                );
                $connection->dropIndex(
                    $installer->getTable($table),
                    $installer->getIdxName(
                        $installer->getTable($table),
                        ['entity_type_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    )
                );
                $connection->dropColumn($installer->getTable($table), 'entity_type_id');
            }

            $connection->dropColumn($installer->getTable('customer_address_entity'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('customer_address_entity'), 'attribute_set_id');

            $connection->dropIndex(
                $installer->getTable('customer_entity'),
                $installer->getIdxName('customer_entity', ['entity_type_id'])
            );
            $connection->dropColumn($installer->getTable('customer_entity'), 'entity_type_id');
            $connection->dropColumn($installer->getTable('customer_entity'), 'attribute_set_id');
        }

        if (version_compare($context->getVersion(), '2.0.0.2') < 0) {
            /**
             * Update 'customer_visitor' table.
             */
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('customer_visitor'),
                    'customer_id',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'after' => 'visitor_id',
                        'comment' => 'Customer ID'
                    ]
                );

            $setup->getConnection()
                ->addIndex(
                    $setup->getTable('customer_visitor'),
                    $setup->getIdxName(
                        $setup->getTable('customer_visitor'),
                        ['customer_id']
                    ),
                    'customer_id'
                );

            /**
             * Create 'customer_log' table.
             */
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('customer_log')
                )
                ->addColumn(
                    'log_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'identity' => true,
                        'primary' => true
                    ],
                    'Log ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Customer ID'
                )
                ->addColumn(
                    'last_login_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => true,
                        'default' => null
                    ],
                    'Last Login Time'
                )
                ->addColumn(
                    'last_logout_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => true,
                        'default' => null
                    ],
                    'Last Logout Time'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $setup->getTable('customer_log'),
                        ['customer_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['customer_id'],
                    [
                        'type' => AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Customer Log Table');

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
