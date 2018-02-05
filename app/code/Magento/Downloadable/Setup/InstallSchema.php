<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Setup;

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
         * Create table 'downloadable_link'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('downloadable_link'))
            ->addColumn(
                'link_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Link ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort order'
            )
            ->addColumn(
                'number_of_downloads',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Number of downloads'
            )
            ->addColumn(
                'is_shareable',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Shareable flag'
            )
            ->addColumn(
                'link_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link Url'
            )
            ->addColumn(
                'link_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link File'
            )
            ->addColumn(
                'link_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                [],
                'Link Type'
            )
            ->addColumn(
                'sample_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Sample Url'
            )
            ->addColumn(
                'sample_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Sample File'
            )
            ->addColumn(
                'sample_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                [],
                'Sample Type'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link', ['product_id', 'sort_order']),
                ['product_id', 'sort_order']
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_link', 'product_id', 'catalog_product_entity', 'entity_id'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Downloadable Link Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'downloadable_link_price'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('downloadable_link_price'))
            ->addColumn(
                'price_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Price ID'
            )
            ->addColumn(
                'link_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Link ID'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website ID'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Price'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_price', 'link_id'),
                'link_id'
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_link_price', 'link_id', 'downloadable_link', 'link_id'),
                'link_id',
                $installer->getTable('downloadable_link'),
                'link_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_price', 'website_id'),
                'website_id'
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_link_price', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Downloadable Link Price Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'downloadable_link_purchased'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('downloadable_link_purchased'))
            ->addColumn(
                'purchased_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Purchased ID'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Order ID'
            )
            ->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Order Increment ID'
            )
            ->addColumn(
                'order_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Order Item ID'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Date of creation'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Date of modification'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Customer ID'
            )
            ->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Product name'
            )
            ->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Product sku'
            )
            ->addColumn(
                'link_section_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link_section_title'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_purchased', 'order_id'),
                'order_id'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_purchased', 'order_item_id'),
                'order_item_id'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_purchased', 'customer_id'),
                'customer_id'
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_link_purchased', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_link_purchased', 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Downloadable Link Purchased Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'downloadable_link_purchased_item'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('downloadable_link_purchased_item'))
            ->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Item ID'
            )
            ->addColumn(
                'purchased_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Purchased ID'
            )
            ->addColumn(
                'order_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Order Item ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'link_hash',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link hash'
            )
            ->addColumn(
                'number_of_downloads_bought',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Number of downloads bought'
            )
            ->addColumn(
                'number_of_downloads_used',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Number of downloads used'
            )
            ->addColumn(
                'link_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Link ID'
            )
            ->addColumn(
                'link_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link Title'
            )
            ->addColumn(
                'is_shareable',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Shareable Flag'
            )
            ->addColumn(
                'link_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link Url'
            )
            ->addColumn(
                'link_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link File'
            )
            ->addColumn(
                'link_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Link Type'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_purchased_item', 'link_hash'),
                'link_hash'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_purchased_item', 'order_item_id'),
                'order_item_id'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_purchased_item', 'purchased_id'),
                'purchased_id'
            )
            ->addForeignKey(
                $installer->getFkName(
                    'downloadable_link_purchased_item',
                    'purchased_id',
                    'downloadable_link_purchased',
                    'purchased_id'
                ),
                'purchased_id',
                $installer->getTable('downloadable_link_purchased'),
                'purchased_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'downloadable_link_purchased_item',
                    'order_item_id',
                    'sales_order_item',
                    'item_id'
                ),
                'order_item_id',
                $installer->getTable('sales_order_item'),
                'item_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Downloadable Link Purchased Item Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'downloadable_link_title'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('downloadable_link_title'))
            ->addColumn(
                'title_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Title ID'
            )
            ->addColumn(
                'link_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Link ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
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
                    'downloadable_link_title',
                    ['link_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['link_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_link_title', 'link_id', 'downloadable_link', 'link_id'),
                'link_id',
                $installer->getTable('downloadable_link'),
                'link_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addIndex(
                $installer->getIdxName('downloadable_link_title', 'store_id'),
                'store_id'
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_link_title', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Link Title Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'downloadable_sample'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('downloadable_sample'))
            ->addColumn(
                'sample_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Sample ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product ID'
            )
            ->addColumn(
                'sample_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Sample URL'
            )
            ->addColumn(
                'sample_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Sample file'
            )
            ->addColumn(
                'sample_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                [],
                'Sample Type'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addIndex(
                $installer->getIdxName('downloadable_sample', 'product_id'),
                'product_id'
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_sample', 'product_id', 'catalog_product_entity', 'entity_id'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Downloadable Sample Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'downloadable_sample_title'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('downloadable_sample_title'))
            ->addColumn(
                'title_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Title ID'
            )
            ->addColumn(
                'sample_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sample ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
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
                    'downloadable_sample_title',
                    ['sample_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['sample_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_sample_title', 'sample_id', 'downloadable_sample', 'sample_id'),
                'sample_id',
                $installer->getTable('downloadable_sample'),
                'sample_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addIndex(
                $installer->getIdxName('downloadable_sample_title', 'store_id'),
                'store_id'
            )
            ->addForeignKey(
                $installer->getFkName('downloadable_sample_title', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Downloadable Sample Title Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_downlod_idx'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_downlod_idx'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group ID'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website ID'
            )
            ->addColumn(
                'min_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Minimum price'
            )
            ->addColumn(
                'max_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Maximum price'
            )
            ->setComment('Indexer Table for price of downloadable products');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'catalog_product_index_price_downlod_tmp'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('catalog_product_index_price_downlod_tmp'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group ID'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website ID'
            )
            ->addColumn(
                'min_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Minimum price'
            )
            ->addColumn(
                'max_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Maximum price'
            )
            ->setOption(
                'type',
                \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
            )
            ->setComment('Temporary Indexer Table for price of downloadable products');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
