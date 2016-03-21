<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Setup;

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
        $setup->startSetup();

        /**
         * Create table 'tax_class'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('tax_class')
        )->addColumn(
            'class_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Class Id'
        )->addColumn(
            'class_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Class Name'
        )->addColumn(
            'class_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            8,
            ['nullable' => false, 'default' => 'CUSTOMER'],
            'Class Type'
        )->setComment(
            'Tax Class'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'tax_calculation_rule'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('tax_calculation_rule')
        )->addColumn(
            'tax_calculation_rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Tax Calculation Rule Id'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'priority',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Priority'
        )->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Position'
        )->addColumn(
            'calculate_subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Calculate off subtotal option'
        )->addIndex(
            $setup->getIdxName('tax_calculation_rule', ['priority', 'position']),
            ['priority', 'position']
        )->addIndex(
            $setup->getIdxName('tax_calculation_rule', ['code']),
            ['code']
        )->setComment(
            'Tax Calculation Rule'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'tax_calculation_rate'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('tax_calculation_rate')
        )->addColumn(
            'tax_calculation_rate_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Tax Calculation Rate Id'
        )->addColumn(
            'tax_country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            ['nullable' => false],
            'Tax Country Id'
        )->addColumn(
            'tax_region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Tax Region Id'
        )->addColumn(
            'tax_postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            21,
            [],
            'Tax Postcode'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'rate',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Rate'
        )->addColumn(
            'zip_is_range',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Zip Is Range'
        )->addColumn(
            'zip_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Zip From'
        )->addColumn(
            'zip_to',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Zip To'
        )->addIndex(
            $setup->getIdxName('tax_calculation_rate', ['tax_country_id', 'tax_region_id', 'tax_postcode']),
            ['tax_country_id', 'tax_region_id', 'tax_postcode']
        )->addIndex(
            $setup->getIdxName('tax_calculation_rate', ['code']),
            ['code']
        )->addIndex(
            $setup->getIdxName(
                'tax_calculation_rate',
                ['tax_calculation_rate_id', 'tax_country_id', 'tax_region_id', 'zip_is_range', 'tax_postcode']
            ),
            ['tax_calculation_rate_id', 'tax_country_id', 'tax_region_id', 'zip_is_range', 'tax_postcode']
        )->setComment(
            'Tax Calculation Rate'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'tax_calculation'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('tax_calculation')
        )->addColumn(
            'tax_calculation_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Tax Calculation Id'
        )->addColumn(
            'tax_calculation_rate_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Tax Calculation Rate Id'
        )->addColumn(
            'tax_calculation_rule_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Tax Calculation Rule Id'
        )->addColumn(
            'customer_tax_class_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Customer Tax Class Id'
        )->addColumn(
            'product_tax_class_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Product Tax Class Id'
        )->addIndex(
            $setup->getIdxName('tax_calculation', ['tax_calculation_rule_id']),
            ['tax_calculation_rule_id']
        )->addIndex(
            $setup->getIdxName('tax_calculation', ['customer_tax_class_id']),
            ['customer_tax_class_id']
        )->addIndex(
            $setup->getIdxName('tax_calculation', ['product_tax_class_id']),
            ['product_tax_class_id']
        )->addIndex(
            $setup->getIdxName(
                'tax_calculation',
                ['tax_calculation_rate_id', 'customer_tax_class_id', 'product_tax_class_id']
            ),
            ['tax_calculation_rate_id', 'customer_tax_class_id', 'product_tax_class_id']
        )->addForeignKey(
            $setup->getFkName('tax_calculation', 'product_tax_class_id', 'tax_class', 'class_id'),
            'product_tax_class_id',
            $setup->getTable('tax_class'),
            'class_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('tax_calculation', 'customer_tax_class_id', 'tax_class', 'class_id'),
            'customer_tax_class_id',
            $setup->getTable('tax_class'),
            'class_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                'tax_calculation',
                'tax_calculation_rate_id',
                'tax_calculation_rate',
                'tax_calculation_rate_id'
            ),
            'tax_calculation_rate_id',
            $setup->getTable('tax_calculation_rate'),
            'tax_calculation_rate_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                'tax_calculation',
                'tax_calculation_rule_id',
                'tax_calculation_rule',
                'tax_calculation_rule_id'
            ),
            'tax_calculation_rule_id',
            $setup->getTable('tax_calculation_rule'),
            'tax_calculation_rule_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Tax Calculation'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'tax_calculation_rate_title'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('tax_calculation_rate_title')
        )->addColumn(
            'tax_calculation_rate_title_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Tax Calculation Rate Title Id'
        )->addColumn(
            'tax_calculation_rate_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Tax Calculation Rate Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Value'
        )->addIndex(
            $setup->getIdxName('tax_calculation_rate_title', ['tax_calculation_rate_id', 'store_id']),
            ['tax_calculation_rate_id', 'store_id']
        )->addIndex(
            $setup->getIdxName('tax_calculation_rate_title', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $setup->getFkName('tax_calculation_rate_title', 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                'tax_calculation_rate_title',
                'tax_calculation_rate_id',
                'tax_calculation_rate',
                'tax_calculation_rate_id'
            ),
            'tax_calculation_rate_id',
            $setup->getTable('tax_calculation_rate'),
            'tax_calculation_rate_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Tax Calculation Rate Title'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'tax_order_aggregated_created'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('tax_order_aggregated_created')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'period',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => true],
            'Period'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'order_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Order Status'
        )->addColumn(
            'percent',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            [],
            'Percent'
        )->addColumn(
            'orders_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Orders Count'
        )->addColumn(
            'tax_base_amount_sum',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            [],
            'Tax Base Amount Sum'
        )->addIndex(
            $setup->getIdxName(
                'tax_order_aggregated_created',
                ['period', 'store_id', 'code', 'percent', 'order_status'],
                true
            ),
            ['period', 'store_id', 'code', 'percent', 'order_status'],
            ['type' => 'unique']
        )->addIndex(
            $setup->getIdxName('tax_order_aggregated_created', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $setup->getFkName('tax_order_aggregated_created', 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Tax Order Aggregation'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'tax_order_aggregated_updated'
         */
        $setup->getConnection()->createTable(
            $setup->getConnection()->createTableByDdl(
                $setup->getTable('tax_order_aggregated_created'),
                $setup->getTable('tax_order_aggregated_updated')
            )
        );

        $setup->endSetup();
    }
}
