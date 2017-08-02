<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $customerGroupTable = $setup->getConnection()->describeTable($setup->getTable('customer_group'));
        $customerGroupIdType = $customerGroupTable['customer_group_id']['DATA_TYPE'] == 'int'
            ? \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER : $customerGroupTable['customer_group_id']['DATA_TYPE'];
        /**
         * Create table 'catalogrule'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalogrule'))
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Name'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Description'
            )
            ->addColumn(
                'from_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'From'
            )
            ->addColumn(
                'to_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'To'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Active'
            )
            ->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Conditions Serialized'
            )
            ->addColumn(
                'actions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Actions Serialized'
            )
            ->addColumn(
                'stop_rules_processing',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Stop Rules Processing'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addColumn(
                'simple_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Simple Action'
            )
            ->addColumn(
                'discount_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => 0.0000],
                'Discount Amount'
            )
            ->addColumn(
                'sub_is_enable',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Rule Enable For Subitems'
            )
            ->addColumn(
                'sub_simple_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Simple Action For Subitems'
            )
            ->addColumn(
                'sub_discount_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => 0.0000],
                'Discount Amount For Subitems'
            )
            ->addIndex(
                $installer->getIdxName('catalogrule', ['is_active', 'sort_order', 'to_date', 'from_date']),
                ['is_active', 'sort_order', 'to_date', 'from_date']
            )
            ->setComment('CatalogRule');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalogrule_product'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalogrule_product'))
            ->addColumn(
                'rule_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Product Id'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addColumn(
                'from_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'From Time'
            )
            ->addColumn(
                'to_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'To time'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Group Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
            )
            ->addColumn(
                'action_operator',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => 'to_fixed'],
                'Action Operator'
            )
            ->addColumn(
                'action_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Action Amount'
            )
            ->addColumn(
                'action_stop',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Action Stop'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
            )
            ->addColumn(
                'sub_simple_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Simple Action For Subitems'
            )
            ->addColumn(
                'sub_discount_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Discount Amount For Subitems'
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalogrule_product',
                    ['rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'],
                    true
                ),
                ['rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'],
                ['type' => 'unique']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['customer_group_id']),
                ['customer_group_id']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['website_id']),
                ['website_id']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['from_time']),
                ['from_time']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['to_time']),
                ['to_time']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['product_id']),
                ['product_id']
            )
            ->setComment('CatalogRule Product');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalogrule_product_price'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalogrule_product_price'))
            ->addColumn(
                'rule_product_price_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Product PriceId'
            )
            ->addColumn(
                'rule_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => false],
                'Rule Date'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Group Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
            )
            ->addColumn(
                'rule_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Rule Price'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
            )
            ->addColumn(
                'latest_start_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Latest StartDate'
            )
            ->addColumn(
                'earliest_end_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Earliest EndDate'
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalogrule_product_price',
                    ['rule_date', 'website_id', 'customer_group_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['rule_date', 'website_id', 'customer_group_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product_price', ['customer_group_id']),
                ['customer_group_id']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product_price', ['website_id']),
                ['website_id']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_product_price', ['product_id']),
                ['product_id']
            )
            ->setComment('CatalogRule Product Price');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalogrule_group_website'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalogrule_group_website'))
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Rule Id'
            )
            ->addColumn(
                'customer_group_id',
                $customerGroupIdType,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Website Id'
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_group_website', ['customer_group_id']),
                ['customer_group_id']
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_group_website', ['website_id']),
                ['website_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalogrule_group_website',
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('catalogrule_group_website', 'rule_id', 'catalogrule', 'rule_id'),
                'rule_id',
                $installer->getTable('catalogrule'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('catalogrule_group_website', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('CatalogRule Group Website');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalogrule_website'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalogrule_website'))
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_website', ['website_id']),
                ['website_id']
            )
            ->addForeignKey(
                $installer->getFkName('catalogrule_website', 'rule_id', 'catalogrule', 'rule_id'),
                'rule_id',
                $installer->getTable('catalogrule'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('catalogrule_website', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Rules To Websites Relations');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalogrule_customer_group'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalogrule_customer_group'))
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'customer_group_id',
                $customerGroupIdType,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addIndex(
                $installer->getIdxName('catalogrule_customer_group', ['customer_group_id']),
                ['customer_group_id']
            )
            ->addForeignKey(
                $installer->getFkName('catalogrule_customer_group', 'rule_id', 'catalogrule', 'rule_id'),
                'rule_id',
                $installer->getTable('catalogrule'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalogrule_customer_group',
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Rules To Customer Groups Relations');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
