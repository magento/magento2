<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup;

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
         * Create table 'eav_entity_type'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_type')
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Type Id'
        )->addColumn(
            'entity_type_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Entity Type Code'
        )->addColumn(
            'entity_model',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Entity Model'
        )->addColumn(
            'attribute_model',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Attribute Model'
        )->addColumn(
            'entity_table',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Entity Table'
        )->addColumn(
            'value_table_prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Value Table Prefix'
        )->addColumn(
            'entity_id_field',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Entity Id Field'
        )->addColumn(
            'is_data_sharing',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Defines Is Data Sharing'
        )->addColumn(
            'data_sharing_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['default' => 'default'],
            'Data Sharing Key'
        )->addColumn(
            'default_attribute_set_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Default Attribute Set Id'
        )->addColumn(
            'increment_model',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => false],
            'Increment Model'
        )->addColumn(
            'increment_per_store',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Increment Per Store'
        )->addColumn(
            'increment_pad_length',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '8'],
            'Increment Pad Length'
        )->addColumn(
            'increment_pad_char',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            1,
            ['nullable' => false, 'default' => '0'],
            'Increment Pad Char'
        )->addColumn(
            'additional_attribute_table',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => false],
            'Additional Attribute Table'
        )->addColumn(
            'entity_attribute_collection',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Entity Attribute Collection'
        )->addIndex(
            $installer->getIdxName('eav_entity_type', ['entity_type_code']),
            ['entity_type_code']
        )->setComment(
            'Eav Entity Type'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_set_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Set Id'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => true, 'default' => null],
            'Increment Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Parent Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
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
            'Defines Is Entity Active'
        )->addIndex(
            $installer->getIdxName('eav_entity', ['entity_type_id']),
            ['entity_type_id']
        )->addIndex(
            $installer->getIdxName('eav_entity', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('eav_entity', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity_datetime'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_datetime')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
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
            'Attribute Value'
        )->addIndex(
            $installer->getIdxName('eav_entity_datetime', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('eav_entity_datetime', ['attribute_id', 'value']),
            ['attribute_id', 'value']
        )->addIndex(
            $installer->getIdxName('eav_entity_datetime', ['entity_type_id', 'value']),
            ['entity_type_id', 'value']
        )->addIndex(
            $installer->getIdxName(
                'eav_entity_datetime',
                ['entity_id', 'attribute_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('eav_entity_datetime', 'entity_id', 'eav_entity', 'entity_id'),
            'entity_id',
            $installer->getTable('eav_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_datetime', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_datetime', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity Value Prefix'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity_decimal'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_decimal')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
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
            'Attribute Value'
        )->addIndex(
            $installer->getIdxName('eav_entity_decimal', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('eav_entity_decimal', ['attribute_id', 'value']),
            ['attribute_id', 'value']
        )->addIndex(
            $installer->getIdxName('eav_entity_decimal', ['entity_type_id', 'value']),
            ['entity_type_id', 'value']
        )->addIndex(
            $installer->getIdxName(
                'eav_entity_decimal',
                ['entity_id', 'attribute_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('eav_entity_decimal', 'entity_id', 'eav_entity', 'entity_id'),
            'entity_id',
            $installer->getTable('eav_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_decimal', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_decimal', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity Value Prefix'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity_int'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_int')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Attribute Value'
        )->addIndex(
            $installer->getIdxName('eav_entity_int', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('eav_entity_int', ['attribute_id', 'value']),
            ['attribute_id', 'value']
        )->addIndex(
            $installer->getIdxName('eav_entity_int', ['entity_type_id', 'value']),
            ['entity_type_id', 'value']
        )->addIndex(
            $installer->getIdxName(
                'eav_entity_int',
                ['entity_id', 'attribute_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('eav_entity_int', 'entity_id', 'eav_entity', 'entity_id'),
            'entity_id',
            $installer->getTable('eav_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_int', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_int', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity Value Prefix'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity_text'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_text')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => false],
            'Attribute Value'
        )->addIndex(
            $installer->getIdxName('eav_entity_text', ['entity_type_id']),
            ['entity_type_id']
        )->addIndex(
            $installer->getIdxName('eav_entity_text', ['attribute_id']),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName('eav_entity_text', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName(
                'eav_entity_text',
                ['entity_id', 'attribute_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('eav_entity_text', 'entity_id', 'eav_entity', 'entity_id'),
            'entity_id',
            $installer->getTable('eav_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_text', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_text', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity Value Prefix'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity_varchar'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_varchar')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Attribute Value'
        )->addIndex(
            $installer->getIdxName('eav_entity_varchar', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('eav_entity_varchar', ['attribute_id', 'value']),
            ['attribute_id', 'value']
        )->addIndex(
            $installer->getIdxName('eav_entity_varchar', ['entity_type_id', 'value']),
            ['entity_type_id', 'value']
        )->addIndex(
            $installer->getIdxName(
                'eav_entity_varchar',
                ['entity_id', 'attribute_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('eav_entity_varchar', 'entity_id', 'eav_entity', 'entity_id'),
            'entity_id',
            $installer->getTable('eav_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_varchar', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_varchar', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity Value Prefix'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_attribute'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_attribute')
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Attribute Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Attribute Code'
        )->addColumn(
            'attribute_model',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Attribute Model'
        )->addColumn(
            'backend_model',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Backend Model'
        )->addColumn(
            'backend_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            8,
            ['nullable' => false, 'default' => 'static'],
            'Backend Type'
        )->addColumn(
            'backend_table',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Backend Table'
        )->addColumn(
            'frontend_model',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Frontend Model'
        )->addColumn(
            'frontend_input',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Frontend Input'
        )->addColumn(
            'frontend_label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Frontend Label'
        )->addColumn(
            'frontend_class',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Frontend Class'
        )->addColumn(
            'source_model',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Source Model'
        )->addColumn(
            'is_required',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Defines Is Required'
        )->addColumn(
            'is_user_defined',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Defines Is User Defined'
        )->addColumn(
            'default_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Default Value'
        )->addColumn(
            'is_unique',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Defines Is Unique'
        )->addColumn(
            'note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Note'
        )->addIndex(
            $installer->getIdxName(
                'eav_attribute',
                ['entity_type_id', 'attribute_code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_type_id', 'attribute_code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('eav_attribute', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Attribute'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_store')
        )->addColumn(
            'entity_store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Store Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'increment_prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => true],
            'Increment Prefix'
        )->addColumn(
            'increment_last_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => true],
            'Last Incremented Id'
        )->addIndex(
            $installer->getIdxName('eav_entity_store', ['entity_type_id']),
            ['entity_type_id']
        )->addIndex(
            $installer->getIdxName('eav_entity_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('eav_entity_store', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_entity_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity Store'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_attribute_set'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_attribute_set')
        )->addColumn(
            'attribute_set_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Attribute Set Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_set_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Attribute Set Name'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName(
                'eav_attribute_set',
                ['entity_type_id', 'attribute_set_name'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_type_id', 'attribute_set_name'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('eav_attribute_set', ['entity_type_id', 'sort_order']),
            ['entity_type_id', 'sort_order']
        )->addForeignKey(
            $installer->getFkName('eav_attribute_set', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Attribute Set'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_attribute_group'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_attribute_group')
        )->addColumn(
            'attribute_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Attribute Group Id'
        )->addColumn(
            'attribute_set_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Set Id'
        )->addColumn(
            'attribute_group_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Attribute Group Name'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addColumn(
            'default_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Default Id'
        )->addColumn(
            'attribute_group_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Attribute Group Code'
        )->addColumn(
            'tab_group_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => null],
            'Tab Group Code'
        )->addIndex(
            $installer->getIdxName(
                'eav_attribute_group',
                ['attribute_set_id', 'attribute_group_name'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['attribute_set_id', 'attribute_group_name'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('eav_attribute_group', ['attribute_set_id', 'sort_order']),
            ['attribute_set_id', 'sort_order']
        )->addForeignKey(
            $installer->getFkName('eav_attribute_group', 'attribute_set_id', 'eav_attribute_set', 'attribute_set_id'),
            'attribute_set_id',
            $installer->getTable('eav_attribute_set'),
            'attribute_set_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Attribute Group'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_entity_attribute'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_entity_attribute')
        )->addColumn(
            'entity_attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Attribute Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type Id'
        )->addColumn(
            'attribute_set_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Set Id'
        )->addColumn(
            'attribute_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Group Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName(
                'eav_entity_attribute',
                ['attribute_set_id', 'attribute_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['attribute_set_id', 'attribute_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(
                'eav_entity_attribute',
                ['attribute_group_id', 'attribute_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['attribute_group_id', 'attribute_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('eav_entity_attribute', ['attribute_set_id', 'sort_order']),
            ['attribute_set_id', 'sort_order']
        )->addIndex(
            $installer->getIdxName('eav_entity_attribute', ['attribute_id']),
            ['attribute_id']
        )->addForeignKey(
            $installer->getFkName('eav_entity_attribute', 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'eav_entity_attribute',
                'attribute_group_id',
                'eav_attribute_group',
                'attribute_group_id'
            ),
            'attribute_group_id',
            $installer->getTable('eav_attribute_group'),
            'attribute_group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Entity Attributes'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_attribute_option'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_attribute_option')
        )->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Option Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName('eav_attribute_option', ['attribute_id']),
            ['attribute_id']
        )->addForeignKey(
            $installer->getFkName('eav_attribute_option', 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Attribute Option'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_attribute_option_value'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_attribute_option_value')
        )->addColumn(
            'value_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Option Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Value'
        )->addIndex(
            $installer->getIdxName('eav_attribute_option_value', ['option_id']),
            ['option_id']
        )->addIndex(
            $installer->getIdxName('eav_attribute_option_value', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('eav_attribute_option_value', 'option_id', 'eav_attribute_option', 'option_id'),
            'option_id',
            $installer->getTable('eav_attribute_option'),
            'option_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_attribute_option_value', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Attribute Option Value'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_attribute_label'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_attribute_label')
        )->addColumn(
            'attribute_label_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Attribute Label Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Value'
        )->addIndex(
            $installer->getIdxName('eav_attribute_label', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('eav_attribute_label', ['attribute_id', 'store_id']),
            ['attribute_id', 'store_id']
        )->addForeignKey(
            $installer->getFkName('eav_attribute_label', 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_attribute_label', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Attribute Label'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_form_type'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_form_type')
        )->addColumn(
            'type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Type Id'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Label'
        )->addColumn(
            'is_system',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is System'
        )->addColumn(
            'theme',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => true],
            'Theme'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addIndex(
            $installer->getIdxName(
                'eav_form_type',
                ['code', 'theme', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['code', 'theme', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('eav_form_type', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('eav_form_type', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Form Type'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_form_type_entity'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_form_type_entity')
        )->addColumn(
            'type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Type Id'
        )->addColumn(
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Type Id'
        )->addIndex(
            $installer->getIdxName('eav_form_type_entity', ['entity_type_id']),
            ['entity_type_id']
        )->addForeignKey(
            $installer->getFkName('eav_form_type_entity', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
            'entity_type_id',
            $installer->getTable('eav_entity_type'),
            'entity_type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_form_type_entity', 'type_id', 'eav_form_type', 'type_id'),
            'type_id',
            $installer->getTable('eav_form_type'),
            'type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Form Type Entity'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_form_fieldset'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_form_fieldset')
        )->addColumn(
            'fieldset_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Fieldset Id'
        )->addColumn(
            'type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Type Id'
        )->addColumn(
            'code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName(
                'eav_form_fieldset',
                ['type_id', 'code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['type_id', 'code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('eav_form_fieldset', 'type_id', 'eav_form_type', 'type_id'),
            'type_id',
            $installer->getTable('eav_form_type'),
            'type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Form Fieldset'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_form_fieldset_label'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_form_fieldset_label')
        )->addColumn(
            'fieldset_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Fieldset Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store Id'
        )->addColumn(
            'label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Label'
        )->addIndex(
            $installer->getIdxName('eav_form_fieldset_label', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('eav_form_fieldset_label', 'fieldset_id', 'eav_form_fieldset', 'fieldset_id'),
            'fieldset_id',
            $installer->getTable('eav_form_fieldset'),
            'fieldset_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_form_fieldset_label', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Form Fieldset Label'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'eav_form_element'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('eav_form_element')
        )->addColumn(
            'element_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Element Id'
        )->addColumn(
            'type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Type Id'
        )->addColumn(
            'fieldset_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Fieldset Id'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Attribute Id'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName(
                'eav_form_element',
                ['type_id', 'attribute_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['type_id', 'attribute_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('eav_form_element', ['fieldset_id']),
            ['fieldset_id']
        )->addIndex(
            $installer->getIdxName('eav_form_element', ['attribute_id']),
            ['attribute_id']
        )->addForeignKey(
            $installer->getFkName('eav_form_element', 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('eav_form_element', 'fieldset_id', 'eav_form_fieldset', 'fieldset_id'),
            'fieldset_id',
            $installer->getTable('eav_form_fieldset'),
            'fieldset_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName('eav_form_element', 'type_id', 'eav_form_type', 'type_id'),
            'type_id',
            $installer->getTable('eav_form_type'),
            'type_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Eav Form Element'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
