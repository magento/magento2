<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup;

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

        /**
         * Create table 'directory_country'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('directory_country')
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            ['nullable' => false, 'primary' => true, 'default' => false],
            'Country Id in ISO-2'
        )->addColumn(
            'iso2_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            ['nullable' => true, 'default' => null],
            'Country ISO-2 format'
        )->addColumn(
            'iso3_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            ['nullable' => true, 'default' => null],
            'Country ISO-3'
        )->setComment(
            'Directory Country'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'directory_country_format'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('directory_country_format')
        )->addColumn(
            'country_format_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Country Format Id'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            ['nullable' => true, 'default' => null],
            'Country Id in ISO-2'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            30,
            ['nullable' => true, 'default' => null],
            'Country Format Type'
        )->addColumn(
            'format',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => false],
            'Country Format'
        )->addIndex(
            $installer->getIdxName(
                'directory_country_format',
                ['country_id', 'type'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['country_id', 'type'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Directory Country Format'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'directory_country_region'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('directory_country_region')
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Region Id'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            ['nullable' => false, 'default' => '0'],
            'Country Id in ISO-2'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => true, 'default' => null],
            'Region code'
        )->addColumn(
            'default_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Region Name'
        )->addIndex(
            $installer->getIdxName('directory_country_region', ['country_id']),
            ['country_id']
        )->setComment(
            'Directory Country Region'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'directory_country_region_name'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('directory_country_region_name')
        )->addColumn(
            'locale',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            8,
            ['nullable' => false, 'primary' => true, 'default' => false],
            'Locale'
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
            'Region Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Region Name'
        )->addIndex(
            $installer->getIdxName('directory_country_region_name', ['region_id']),
            ['region_id']
        )->addForeignKey(
            $installer->getFkName(
                'directory_country_region_name',
                'region_id',
                'directory_country_region',
                'region_id'
            ),
            'region_id',
            $installer->getTable('directory_country_region'),
            'region_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Directory Country Region Name'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'directory_currency_rate'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('directory_currency_rate')
        )->addColumn(
            'currency_from',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            ['nullable' => false, 'primary' => true, 'default' => false],
            'Currency Code Convert From'
        )->addColumn(
            'currency_to',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            ['nullable' => false, 'primary' => true, 'default' => false],
            'Currency Code Convert To'
        )->addColumn(
            'rate',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '24,12',
            ['nullable' => false, 'default' => '0.000000000000'],
            'Currency Conversion Rate'
        )->addIndex(
            $installer->getIdxName('directory_currency_rate', ['currency_to']),
            ['currency_to']
        )->setComment(
            'Directory Currency Rate'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
