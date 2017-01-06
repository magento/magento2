<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Setup;

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
        /*
         * Prepare database for tables install
         */
        $installer->startSetup();

        /**
         * Create table 'report_compared_product_index'.
         * In MySQL version this table comes with unique keys to implement insertOnDuplicate(), so that
         * only one record is added when customer/visitor compares same product again.
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('report_compared_product_index'))
            ->addColumn(
                'index_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Index Id'
            )
            ->addColumn(
                'visitor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Visitor Id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Id'
            )
            ->addColumn(
                'added_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Added At'
            )
            ->addIndex(
                $installer->getIdxName(
                    'report_compared_product_index',
                    ['visitor_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['visitor_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    'report_compared_product_index',
                    ['customer_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['customer_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('report_compared_product_index', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('report_compared_product_index', ['added_at']),
                ['added_at']
            )
            ->addIndex(
                $installer->getIdxName('report_compared_product_index', ['product_id']),
                ['product_id']
            )
            ->addForeignKey(
                $installer->getFkName('report_compared_product_index', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'report_compared_product_index',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('report_compared_product_index', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Reports Compared Product Index Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'report_viewed_product_index'
         * In MySQL version this table comes with unique keys to implement insertOnDuplicate(), so that
         * only one record is added when customer/visitor views same product again.
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('report_viewed_product_index'))
            ->addColumn(
                'index_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Index Id'
            )
            ->addColumn(
                'visitor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Visitor Id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Id'
            )
            ->addColumn(
                'added_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Added At'
            )
            ->addIndex(
                $installer->getIdxName(
                    'report_viewed_product_index',
                    ['visitor_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['visitor_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    'report_viewed_product_index',
                    ['customer_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['customer_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_index', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_index', ['added_at']),
                ['added_at']
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_index', ['product_id']),
                ['product_id']
            )
            ->addForeignKey(
                $installer->getFkName('report_viewed_product_index', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'report_viewed_product_index',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('report_viewed_product_index', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Reports Viewed Product Index Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'report_event_types'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('report_event_types'))
            ->addColumn(
                'event_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Event Type Id'
            )
            ->addColumn(
                'event_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Event Name'
            )
            ->addColumn(
                'customer_login',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Login'
            )
            ->setComment('Reports Event Type Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'report_event'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('report_event'))
            ->addColumn(
                'event_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Event Id'
            )
            ->addColumn(
                'logged_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Logged At'
            )
            ->addColumn(
                'event_type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Event Type Id'
            )
            ->addColumn(
                'object_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Object Id'
            )
            ->addColumn(
                'subject_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Subject Id'
            )
            ->addColumn(
                'subtype',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Subtype'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addIndex(
                $installer->getIdxName('report_event', ['event_type_id']),
                ['event_type_id']
            )
            ->addIndex(
                $installer->getIdxName('report_event', ['subject_id']),
                ['subject_id']
            )
            ->addIndex(
                $installer->getIdxName('report_event', ['object_id']),
                ['object_id']
            )
            ->addIndex(
                $installer->getIdxName('report_event', ['subtype']),
                ['subtype']
            )
            ->addIndex(
                $installer->getIdxName('report_event', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('report_event', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('report_event', 'event_type_id', 'report_event_types', 'event_type_id'),
                'event_type_id',
                $installer->getTable('report_event_types'),
                'event_type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Reports Event Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'report_compared_product_index'.
         * MySQL table differs by having unique keys on (customer/visitor, product) columns and is created
         * in separate install.
         */
        $tableName = $installer->getTable('report_compared_product_index');
        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'index_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Index Id'
                )
                ->addColumn(
                    'visitor_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Visitor Id'
                )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Customer Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true],
                    'Store Id'
                )
                ->addColumn(
                    'added_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Added At'
                )
                ->addIndex(
                    $installer->getIdxName('report_compared_product_index', ['visitor_id', 'product_id']),
                    ['visitor_id', 'product_id']
                )
                ->addIndex(
                    $installer->getIdxName('report_compared_product_index', ['customer_id', 'product_id']),
                    ['customer_id', 'product_id']
                )
                ->addIndex(
                    $installer->getIdxName('report_compared_product_index', ['store_id']),
                    ['store_id']
                )
                ->addIndex(
                    $installer->getIdxName('report_compared_product_index', ['added_at']),
                    ['added_at']
                )
                ->addIndex(
                    $installer->getIdxName('report_compared_product_index', ['product_id']),
                    ['product_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'report_compared_product_index',
                        'customer_id',
                        'customer_entity',
                        'entity_id'
                    ),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'report_compared_product_index',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('report_compared_product_index', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
                )
                ->setComment('Reports Compared Product Index Table');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'report_viewed_product_index'.
         * MySQL table differs by having unique keys on (customer/visitor, product) columns and is created
         * in separate install.
         */
        $tableName = $installer->getTable('report_viewed_product_index');
        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'index_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Index Id'
                )
                ->addColumn(
                    'visitor_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Visitor Id'
                )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Customer Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true],
                    'Store Id'
                )
                ->addColumn(
                    'added_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Added At'
                )
                ->addIndex(
                    $installer->getIdxName('report_viewed_product_index', ['visitor_id', 'product_id']),
                    ['visitor_id', 'product_id']
                )
                ->addIndex(
                    $installer->getIdxName('report_viewed_product_index', ['customer_id', 'product_id']),
                    ['customer_id', 'product_id']
                )
                ->addIndex(
                    $installer->getIdxName('report_viewed_product_index', ['store_id']),
                    ['store_id']
                )
                ->addIndex(
                    $installer->getIdxName('report_viewed_product_index', ['added_at']),
                    ['added_at']
                )
                ->addIndex(
                    $installer->getIdxName('report_viewed_product_index', ['product_id']),
                    ['product_id']
                )
                ->addForeignKey(
                    $installer->getFkName('report_viewed_product_index', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'report_viewed_product_index',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('report_viewed_product_index', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
                )
                ->setComment('Reports Viewed Product Index Table');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'report_viewed_product_aggregated_daily'.
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('report_viewed_product_aggregated_daily'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'period',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Period'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product Id'
            )
            ->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Product Name'
            )
            ->addColumn(
                'product_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Product Price'
            )
            ->addColumn(
                'views_num',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Number of Views'
            )
            ->addColumn(
                'rating_pos',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rating Pos'
            )
            ->addIndex(
                $installer->getIdxName(
                    'report_viewed_product_aggregated_daily',
                    ['period', 'store_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['period', 'store_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_aggregated_daily', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_aggregated_daily', ['product_id']),
                ['product_id']
            )
            ->addForeignKey(
                $installer->getFkName('report_viewed_product_aggregated_daily', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'report_viewed_product_aggregated_daily',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Most Viewed Products Aggregated Daily');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'report_viewed_product_aggregated_monthly'.
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('report_viewed_product_aggregated_monthly'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'period',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Period'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product Id'
            )
            ->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Product Name'
            )
            ->addColumn(
                'product_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Product Price'
            )
            ->addColumn(
                'views_num',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Number of Views'
            )
            ->addColumn(
                'rating_pos',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rating Pos'
            )
            ->addIndex(
                $installer->getIdxName(
                    'report_viewed_product_aggregated_monthly',
                    ['period', 'store_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['period', 'store_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_aggregated_monthly', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_aggregated_monthly', ['product_id']),
                ['product_id']
            )
            ->addForeignKey(
                $installer->getFkName('report_viewed_product_aggregated_monthly', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'report_viewed_product_aggregated_monthly',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Most Viewed Products Aggregated Monthly');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'report_viewed_product_aggregated_yearly'.
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('report_viewed_product_aggregated_yearly'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'period',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Period'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product Id'
            )
            ->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Product Name'
            )
            ->addColumn(
                'product_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Product Price'
            )
            ->addColumn(
                'views_num',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Number of Views'
            )
            ->addColumn(
                'rating_pos',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rating Pos'
            )
            ->addIndex(
                $installer->getIdxName(
                    'report_viewed_product_aggregated_yearly',
                    ['period', 'store_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['period', 'store_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_aggregated_yearly', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('report_viewed_product_aggregated_yearly', ['product_id']),
                ['product_id']
            )
            ->addForeignKey(
                $installer->getFkName('report_viewed_product_aggregated_yearly', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'report_viewed_product_aggregated_yearly',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Most Viewed Products Aggregated Yearly');
        $installer->getConnection()->createTable($table);

        /*
         * Prepare database for tables install
         */
        $installer->endSetup();

    }
}
