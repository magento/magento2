<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestSetupModule2\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

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

        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $connection = $setup->getConnection();

            //add new column
            $setup->getConnection()->addColumn(
                $setup->getTable('setup_tests_entity_table'),
                'group_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Group Id'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('setup_tests_entity_table'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'default' => '0',
                    'comment' => 'Store Id'
                ]
            );

            //add index
            $connection->addIndex(
                $setup->getTable('setup_tests_entity_table'),
                $setup->getIdxName('setup_tests_entity_table', ['store_id']),
                ['store_id']
            );

            //modify existing column with type TEXT/TYPE_TIMESTAMP
            $setup->getConnection()->modifyColumn(
                $setup->getTable('setup_tests_address_entity'),
                'suffix',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                ]
            )->modifyColumn(
                $setup->getTable('setup_tests_entity_table'),
                'created_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                ]
            );

            //addTable
            $table = $setup->getConnection()
                ->newTable($setup->getTable('setup_tests_entity_passwords'))
                ->addColumn(
                    'password_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Password Id'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'User Id'
                )
                ->addColumn(
                    'password_hash',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [],
                    'Password Hash'
                )
                ->addIndex(
                    $setup->getIdxName('setup_tests_entity_passwords', ['entity_id']),
                    ['entity_id']
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'setup_tests_entity_passwords',
                        'entity_id',
                        'setup_tests_entity_table',
                        'entity_id'
                    ),
                    'entity_id',
                    $setup->getTable('setup_tests_entity_table'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Entity Passwords');

            $setup->getConnection()->createTable($table);
            //remove foreign key
            $connection->dropForeignKey(
                $setup->getTable('setup_tests_address_entity_decimal'),
                $setup->getFkName(
                    'setup_tests_address_entity_decimal',
                    'entity_id',
                    'setup_tests_address_entity',
                    'entity_id'
                )
            );

            //remove index
            $connection->dropIndex(
                $setup->getTable('setup_tests_address_entity_decimal'),
                $setup->getIdxName(
                    $setup->getTable('setup_tests_address_entity_decimal'),
                    ['entity_id', 'attribute_id']
                )
            );
            //remove column
            $connection->dropColumn($setup->getTable('setup_tests_entity_table'), 'dob');

            //remove table
            $connection->dropTable($setup->getTable('setup_tests_address_entity_datetime'));
        }

        $setup->endSetup();
    }
}
