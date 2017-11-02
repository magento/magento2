<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestSetupModule2\Setup;

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
         * Create table 'entity_table'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('setup_tests_entity_table')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Website Id'
        )->addColumn(
            'email_field',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Increment Id'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'created_in',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Created From'
        )->addColumn(
            'firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'First Name'
        )->addColumn(
            'lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Last Name'
        )->addColumn(
            'dob',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            [],
            'Date of Birth'
        )->addColumn(
            'default_billing_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'Default Billing Address'
        )->addColumn(
            'default_shipping_address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'Default Shipping Address'
        )->addIndex(
            $installer->getIdxName(
                'setup_tests_entity_table',
                ['email_field', 'website_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['email_field', 'website_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('setup_tests_entity_table', ['website_id']),
            ['website_id']
        )->addIndex(
            $installer->getIdxName('setup_tests_entity_table', ['firstname']),
            ['firstname']
        )->addIndex(
            $installer->getIdxName('setup_tests_entity_table', ['lastname']),
            ['lastname']
        )->addForeignKey(
            $installer->getFkName(
                'setup_tests_entity_table',
                'website_id',
                'store_website',
                'website_id'
            ),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Entity Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'address_entity'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('setup_tests_address_entity')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Increment Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Parent Id'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Active'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'City'
        )->addColumn(
            'company',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Company'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Country'
        )->addColumn(
            'fax',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Fax'
        )->addColumn(
            'firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'First Name'
        )->addColumn(
            'lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Last Name'
        )->addColumn(
            'middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Middle Name'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Zip/Postal Code'
        )->addColumn(
            'prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => true, 'default' => null],
            'Name Prefix'
        )->addColumn(
            'region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'State/Province'
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'State/Province'
        )->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Street Address'
        )->addColumn(
            'suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => true, 'default' => null],
            'Name Suffix'
        )->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Phone Number'
        )->addIndex(
            $installer->getIdxName('address_entity', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName(
                'setup_tests_address_entity',
                'parent_id',
                'setup_tests_entity_table',
                'entity_id'
            ),
            'parent_id',
            $installer->getTable('setup_tests_entity_table'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Address Entity'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'address_entity_datetime'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('setup_tests_address_entity_datetime')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null],
            'Value'
        )->addIndex(
            $installer->getIdxName(
                'setup_tests_address_entity_datetime',
                ['entity_id', 'attribute_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('setup_tests_address_entity_datetime', ['attribute_id']),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName(
                'setup_tests_address_entity_datetime',
                ['entity_id', 'attribute_id', 'value']
            ),
            ['entity_id', 'attribute_id', 'value']
        )->addForeignKey(
            $installer->getFkName(
                'address_entity_datetime',
                'entity_id',
                'address_entity',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('setup_tests_address_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Address Entity Datetime'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'address_entity_decimal'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('setup_tests_address_entity_decimal')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Value'
        )->addIndex(
            $installer->getIdxName(
                'setup_tests_address_entity_decimal',
                ['entity_id', 'attribute_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('setup_tests_address_entity_decimal', ['attribute_id']),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName('setup_tests_address_entity_decimal', ['entity_id', 'attribute_id', 'value']),
            ['entity_id', 'attribute_id', 'value']
        )->addForeignKey(
            $installer->getFkName(
                'setup_tests_address_entity_decimal',
                'entity_id',
                'setup_tests_address_entity',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('setup_tests_address_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Address Entity Decimal'
        );
        $installer->getConnection()->createTable($table);
    }
}
