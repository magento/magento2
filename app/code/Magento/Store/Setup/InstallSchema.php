<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;
use \Magento\Catalog\Setup\InstallData;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $connection = $installer->getConnection();

        /**
         * Create table 'store_website'
         */
        $table = $connection->newTable(
            $installer->getTable('store_website')
        )->addColumn(
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Website Id'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            32,
            [],
            'Code'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            64,
            [],
            'Website Name'
        )->addColumn(
            'sort_order',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addColumn(
            'default_group_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Default Group Id'
        )->addColumn(
            'is_default',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Defines Is Website Default'
        )->addIndex(
            $installer->getIdxName(
                'store_website',
                ['code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('store_website', ['sort_order']),
            ['sort_order']
        )->addIndex(
            $installer->getIdxName('store_website', ['default_group_id']),
            ['default_group_id']
        )->setComment(
            'Websites'
        );
        $connection->createTable($table);

        /**
         * Create table 'store_group'
         */
        $table = $connection->newTable(
            $installer->getTable('store_group')
        )->addColumn(
            'group_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Group Id'
        )->addColumn(
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Website Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Store Group Name'
        )->addColumn(
            'root_category_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Root Category Id'
        )->addColumn(
            'default_store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Default Store Id'
        )->addIndex(
            $installer->getIdxName('store_group', ['website_id']),
            ['website_id']
        )->addIndex(
            $installer->getIdxName('store_group', ['default_store_id']),
            ['default_store_id']
        )->addForeignKey(
            $installer->getFkName('store_group', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Store Groups'
        );
        $connection->createTable($table);

        /**
         * Create table 'store'
         */
        $table = $connection->newTable(
            $installer->getTable('store')
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store Id'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            32,
            [],
            'Code'
        )->addColumn(
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Website Id'
        )->addColumn(
            'group_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Group Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Store Name'
        )->addColumn(
            'sort_order',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Sort Order'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Activity'
        )->addIndex(
            $installer->getIdxName(
                'store',
                ['code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('store', ['website_id']),
            ['website_id']
        )->addIndex(
            $installer->getIdxName('store', ['is_active', 'sort_order']),
            ['is_active', 'sort_order']
        )->addIndex(
            $installer->getIdxName('store', ['group_id']),
            ['group_id']
        )->addForeignKey(
            $installer->getFkName('store', 'group_id', 'store_group', 'group_id'),
            'group_id',
            $installer->getTable('store_group'),
            'group_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('store', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Stores'
        );
        $connection->createTable($table);

        /**
         * Insert websites
         */
        $connection->insertForce(
            $installer->getTable('store_website'),
            [
                'website_id' => 0,
                'code' => 'admin',
                'name' => 'Admin',
                'sort_order' => 0,
                'default_group_id' => 0,
                'is_default' => 0
            ]
        );
        $connection->insertForce(
            $installer->getTable('store_website'),
            [
                'website_id' => 1,
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => 0,
                'default_group_id' => 1,
                'is_default' => 1
            ]
        );

        /**
         * Insert store groups
         */
        $connection->insertForce(
            $installer->getTable('store_group'),
            ['group_id' => 0, 'website_id' => 0, 'name' => 'Default', 'root_category_id' => 0, 'default_store_id' => 0]
        );
        $connection->insertForce(
            $installer->getTable('store_group'),
            [
                'group_id' => 1,
                'website_id' => 1,
                'name' => 'Main Website Store',
                'root_category_id' => 2,
                'default_store_id' => 1
            ]
        );

        /**
         * Insert stores
         */
        $connection->insertForce(
            $installer->getTable('store'),
            [
                'store_id' => 0,
                'code' => 'admin',
                'website_id' => 0,
                'group_id' => 0,
                'name' => 'Admin',
                'sort_order' => 0,
                'is_active' => 1
            ]
        );
        $connection->insertForce(
            $installer->getTable('store'),
            [
                'store_id' => 1,
                'code' => 'default',
                'website_id' => 1,
                'group_id' => 1,
                'name' => 'Default Store View',
                'sort_order' => 0,
                'is_active' => 1
            ]
        );

        $installer->endSetup();

    }
}
