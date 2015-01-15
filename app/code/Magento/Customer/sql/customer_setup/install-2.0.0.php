<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'customer_entity'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_entity')
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
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Website Id'
)->addColumn(
    'email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Email'
)->addColumn(
    'group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Group Id'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Store Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Updated At'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
    'Is Active'
)->addColumn(
    'disable_auto_group_change',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Disable automatic group change based on VAT ID'
)->addIndex(
    $installer->getIdxName('customer_entity', ['store_id']),
    ['store_id']
)->addIndex(
    $installer->getIdxName('customer_entity', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName(
        'customer_entity',
        ['email', 'website_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['email', 'website_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_entity', ['website_id']),
    ['website_id']
)->addForeignKey(
    $installer->getFkName('customer_entity', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Entity'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_address_entity'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_address_entity')
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
    ['nullable' => false],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Updated At'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
    'Is Active'
)->addIndex(
    $installer->getIdxName('customer_address_entity', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $installer->getFkName('customer_address_entity', 'parent_id', 'customer_entity', 'entity_id'),
    'parent_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Address Entity'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_address_entity_datetime'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_address_entity_datetime')
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
        'customer_address_entity_datetime',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_address_entity_datetime', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_datetime', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_datetime', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_address_entity_datetime', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_datetime', 'entity_id', 'customer_address_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_address_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_datetime', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Address Entity Datetime'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_address_entity_decimal'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_address_entity_decimal')
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
        'customer_address_entity_decimal',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_address_entity_decimal', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_decimal', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_decimal', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_address_entity_decimal', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_decimal', 'entity_id', 'customer_address_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_address_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_decimal', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Address Entity Decimal'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_address_entity_int'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_address_entity_int')
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
    'Value'
)->addIndex(
    $installer->getIdxName(
        'customer_address_entity_int',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_address_entity_int', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_int', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_int', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_address_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_int', 'entity_id', 'customer_address_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_address_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_int', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Address Entity Int'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_address_entity_text'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_address_entity_text')
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
    'Value'
)->addIndex(
    $installer->getIdxName(
        'customer_address_entity_text',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_address_entity_text', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_text', ['attribute_id']),
    ['attribute_id']
)->addForeignKey(
    $installer->getFkName('customer_address_entity_text', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_text', 'entity_id', 'customer_address_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_address_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_text', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Address Entity Text'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_address_entity_varchar'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_address_entity_varchar')
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
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Entity Id'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Value'
)->addIndex(
    $installer->getIdxName(
        'customer_address_entity_varchar',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_address_entity_varchar', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_varchar', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_address_entity_varchar', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_address_entity_varchar', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_varchar', 'entity_id', 'customer_address_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_address_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_address_entity_varchar', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Address Entity Varchar'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_entity_datetime'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_entity_datetime')
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
        'customer_entity_datetime',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_entity_datetime', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_entity_datetime', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_entity_datetime', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_entity_datetime', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_datetime', 'entity_id', 'customer_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_datetime', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Entity Datetime'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_entity_decimal'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_entity_decimal')
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
        'customer_entity_decimal',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_entity_decimal', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_entity_decimal', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_entity_decimal', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_entity_decimal', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_decimal', 'entity_id', 'customer_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_decimal', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Entity Decimal'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_entity_int'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_entity_int')
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
    'Value'
)->addIndex(
    $installer->getIdxName(
        'customer_entity_int',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_entity_int', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_entity_int', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_entity_int', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_int', 'entity_id', 'customer_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_int', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Entity Int'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_entity_text'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_entity_text')
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
    'Value'
)->addIndex(
    $installer->getIdxName(
        'customer_entity_text',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_entity_text', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_entity_text', ['attribute_id']),
    ['attribute_id']
)->addForeignKey(
    $installer->getFkName('customer_entity_text', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_text', 'entity_id', 'customer_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_text', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Entity Text'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_entity_varchar'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_entity_varchar')
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
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Entity Id'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Value'
)->addIndex(
    $installer->getIdxName(
        'customer_entity_varchar',
        ['entity_id', 'attribute_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['entity_id', 'attribute_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('customer_entity_varchar', ['entity_type_id']),
    ['entity_type_id']
)->addIndex(
    $installer->getIdxName('customer_entity_varchar', ['attribute_id']),
    ['attribute_id']
)->addIndex(
    $installer->getIdxName('customer_entity_varchar', ['entity_id', 'attribute_id', 'value']),
    ['entity_id', 'attribute_id', 'value']
)->addForeignKey(
    $installer->getFkName('customer_entity_varchar', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_varchar', 'entity_id', 'customer_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_entity_varchar', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Entity Varchar'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_group'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_group')
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Customer Group Id'
)->addColumn(
    'customer_group_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false],
    'Customer Group Code'
)->addColumn(
    'tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Tax Class Id'
)->setComment(
    'Customer Group'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_eav_attribute'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_eav_attribute')
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Attribute Id'
)->addColumn(
    'is_visible',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
    'Is Visible'
)->addColumn(
    'input_filter',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Input Filter'
)->addColumn(
    'multiline_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
    'Multiline Count'
)->addColumn(
    'validate_rules',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Validate Rules'
)->addColumn(
    'is_system',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Is System'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Sort Order'
)->addColumn(
    'data_model',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Data Model'
)->addForeignKey(
    $installer->getFkName('customer_eav_attribute', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Eav Attribute'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_form_attribute'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_form_attribute')
)->addColumn(
    'form_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false, 'primary' => true],
    'Form Code'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Attribute Id'
)->addIndex(
    $installer->getIdxName('customer_form_attribute', ['attribute_id']),
    ['attribute_id']
)->addForeignKey(
    $installer->getFkName('customer_form_attribute', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Form Attribute'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_eav_attribute_website'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_eav_attribute_website')
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Attribute Id'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Website Id'
)->addColumn(
    'is_visible',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Visible'
)->addColumn(
    'is_required',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Required'
)->addColumn(
    'default_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Default Value'
)->addColumn(
    'multiline_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Multiline Count'
)->addIndex(
    $installer->getIdxName('customer_eav_attribute_website', ['website_id']),
    ['website_id']
)->addForeignKey(
    $installer->getFkName('customer_eav_attribute_website', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('customer_eav_attribute_website', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Customer Eav Attribute Website'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'customer_visitor'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('customer_visitor')
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Visitor ID'
)->addColumn(
    'session_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    ['nullable' => true, 'default' => null],
    'Session ID'
)->addColumn(
    'last_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Last Visit Time'
)->setComment(
    'Visitor Table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
