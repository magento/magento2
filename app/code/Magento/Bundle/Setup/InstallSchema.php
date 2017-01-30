<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

        /**
         * Create table 'catalog_product_bundle_option'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_bundle_option'))
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Id'
            )
            ->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Parent Id'
            )
            ->addColumn(
                'required',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Required'
            )
            ->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Position'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Type'
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_bundle_option', ['parent_id']),
                ['parent_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_option',
                    'parent_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'parent_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Bundle Option');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_bundle_option_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_bundle_option_value'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Option Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Title'
            )
            ->addIndex(
                $installer->getIdxName(
                    'catalog_product_bundle_option_value',
                    ['option_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_option_value',
                    'option_id',
                    'catalog_product_bundle_option',
                    'option_id'
                ),
                'option_id',
                $installer->getTable('catalog_product_bundle_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Bundle Option Value');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_bundle_selection'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_bundle_selection'))
            ->addColumn(
                'selection_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Selection Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Option Id'
            )
            ->addColumn(
                'parent_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Parent Product Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Position'
            )
            ->addColumn(
                'is_default',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Default'
            )
            ->addColumn(
                'selection_price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Selection Price Type'
            )
            ->addColumn(
                'selection_price_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Selection Price Value'
            )
            ->addColumn(
                'selection_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Selection Qty'
            )
            ->addColumn(
                'selection_can_change_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Selection Can Change Qty'
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_bundle_selection', ['option_id']),
                ['option_id']
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_bundle_selection', ['product_id']),
                ['product_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_selection',
                    'option_id',
                    'catalog_product_bundle_option',
                    'option_id'
                ),
                'option_id',
                $installer->getTable('catalog_product_bundle_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_selection',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Bundle Selection');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_bundle_selection_price'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_bundle_selection_price'))
            ->addColumn(
                'selection_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Selection Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'selection_price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Selection Price Type'
            )
            ->addColumn(
                'selection_price_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Selection Price Value'
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_bundle_selection_price', ['website_id']),
                ['website_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_selection_price',
                    'website_id',
                    'store_website',
                    'website_id'
                ),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_selection_price',
                    'selection_id',
                    'catalog_product_bundle_selection',
                    'selection_id'
                ),
                'selection_id',
                $installer->getTable('catalog_product_bundle_selection'),
                'selection_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Bundle Selection Price');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_bundle_price_index'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_bundle_price_index'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addColumn(
                'min_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Min Price'
            )
            ->addColumn(
                'max_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Max Price'
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_bundle_price_index', ['website_id']),
                ['website_id']
            )
            ->addIndex(
                $installer->getIdxName('catalog_product_bundle_price_index', ['customer_group_id']),
                ['customer_group_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_price_index',
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
                $installer->getFkName(
                    'catalog_product_bundle_price_index',
                    'entity_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'catalog_product_bundle_price_index',
                    'website_id',
                    'store_website',
                    'website_id'
                ),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Catalog Product Bundle Price Index');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_bundle_stock_index'
         */
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable('catalog_product_bundle_stock_index')
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'stock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Stock Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Option Id'
            )
            ->addColumn(
                'stock_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['default' => '0'],
                'Stock Status'
            )
            ->setComment('Catalog Product Bundle Stock Index');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_bundle_idx'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_bundle_idx'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'tax_class_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Tax Class Id'
            )
            ->addColumn(
                'price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Price Type'
            )
            ->addColumn(
                'special_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Special Price'
            )
            ->addColumn(
                'tier_percent',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Percent'
            )
            ->addColumn(
                'orig_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Orig Price'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Price'
            )
            ->addColumn(
                'min_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Min Price'
            )
            ->addColumn(
                'max_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Max Price'
            )
            ->addColumn(
                'tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Price'
            )
            ->addColumn(
                'base_tier',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Tier'
            )
            ->setComment('Catalog Product Index Price Bundle Idx');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_bundle_tmp'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_bundle_tmp'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'tax_class_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Tax Class Id'
            )
            ->addColumn(
                'price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Price Type'
            )
            ->addColumn(
                'special_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Special Price'
            )
            ->addColumn(
                'tier_percent',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Percent'
            )
            ->addColumn(
                'orig_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Orig Price'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Price'
            )
            ->addColumn(
                'min_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Min Price'
            )
            ->addColumn(
                'max_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Max Price'
            )
            ->addColumn(
                'tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Price'
            )
            ->addColumn(
                'base_tier',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Base Tier'
            )
            ->setOption(
                'type',
                \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
            )
            ->setComment('Catalog Product Index Price Bundle Tmp');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_bundle_sel_idx'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_bundle_sel_idx'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Option Id'
            )
            ->addColumn(
                'selection_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Selection Id'
            )
            ->addColumn(
                'group_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Group Type'
            )
            ->addColumn(
                'is_required',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Required'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Price'
            )
            ->addColumn(
                'tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Price'
            )
            ->setComment('Catalog Product Index Price Bundle Sel Idx');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_bundle_sel_tmp'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_bundle_sel_tmp'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Option Id'
            )
            ->addColumn(
                'selection_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Selection Id'
            )
            ->addColumn(
                'group_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Group Type'
            )
            ->addColumn(
                'is_required',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Is Required'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Price'
            )
            ->addColumn(
                'tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Price'
            )
            ->setOption(
                'type',
                \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
            )
            ->setComment('Catalog Product Index Price Bundle Sel Tmp');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_bundle_opt_idx'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_bundle_opt_idx'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Option Id'
            )
            ->addColumn(
                'min_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Min Price'
            )
            ->addColumn(
                'alt_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Alt Price'
            )
            ->addColumn(
                'max_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Max Price'
            )
            ->addColumn(
                'tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Price'
            )
            ->addColumn(
                'alt_tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Alt Tier Price'
            )
            ->setComment('Catalog Product Index Price Bundle Opt Idx');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_bundle_opt_tmp'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_bundle_opt_tmp'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Option Id'
            )
            ->addColumn(
                'min_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Min Price'
            )
            ->addColumn(
                'alt_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Alt Price'
            )
            ->addColumn(
                'max_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Max Price'
            )
            ->addColumn(
                'tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Tier Price'
            )
            ->addColumn(
                'alt_tier_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Alt Tier Price'
            )
            ->setOption(
                'type',
                \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
            )
            ->setComment('Catalog Product Index Price Bundle Opt Tmp');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
