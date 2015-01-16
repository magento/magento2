<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'catalog_product_entity'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_entity'))
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_set_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute Set ID'
    )
    ->addColumn(
        'type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        32,
        ['nullable' => false, 'default' => 'simple'],
        'Type ID'
    )
    ->addColumn(
        'sku',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        64,
        [],
        'SKU'
    )
    ->addColumn(
        'has_options',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['nullable' => false, 'default' => '0'],
        'Has Options'
    )
    ->addColumn(
        'required_options',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Required Options'
    )
    ->addColumn(
        'created_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        [],
        'Creation Time'
    )
    ->addColumn(
        'updated_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        [],
        'Update Time'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity', ['entity_type_id']),
        ['entity_type_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity', ['attribute_set_id']),
        ['attribute_set_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity', ['sku']),
        ['sku']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity', 'attribute_set_id', 'eav_attribute_set', 'attribute_set_id'),
        'attribute_set_id',
        $installer->getTable('eav_attribute_set'),
        'attribute_set_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
        'entity_type_id',
        $installer->getTable('eav_entity_type'),
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_datetime'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_entity_datetime'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
        null,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_datetime',
            ['entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_datetime', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_datetime', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_datetime', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_datetime', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_datetime', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product Datetime Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_decimal'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_entity_decimal'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_decimal',
            ['entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_decimal', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_decimal', ['attribute_id']),
        ['attribute_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_decimal', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_decimal', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_decimal', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product Decimal Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_int'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_entity_int'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_int',
            ['entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_int', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_int', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_int', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_int', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product Integer Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_text'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_entity_text'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        '64k',
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_text',
            ['entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_text', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_text', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_text', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_text', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_text', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product Text Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_varchar'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_entity_varchar'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_varchar',
            ['entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_varchar', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_varchar', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_varchar', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_varchar', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_varchar', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product Varchar Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_gallery'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_product_entity_gallery'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'position',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'default' => '0'],
        'Position'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        ['nullable' => true, 'default' => null],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_gallery',
            ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_gallery', ['entity_id']),
        ['entity_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_gallery', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_gallery', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_gallery', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_gallery', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_gallery', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product Gallery Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_entity'))
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_set_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attriute Set ID'
    )
    ->addColumn(
        'parent_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Parent Category ID'
    )
    ->addColumn(
        'created_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        [],
        'Creation Time'
    )
    ->addColumn(
        'updated_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        [],
        'Update Time'
    )
    ->addColumn(
        'path',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        ['nullable' => false],
        'Tree Path'
    )
    ->addColumn(
        'position',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false],
        'Position'
    )
    ->addColumn(
        'level',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'default' => '0'],
        'Tree Level'
    )
    ->addColumn(
        'children_count',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false],
        'Child Count'
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity', ['level']),
        ['level']
    )
    ->setComment('Catalog Category Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_datetime'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_entity_datetime'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
        null,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_category_entity_datetime',
            ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_datetime', ['entity_id']),
        ['entity_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_datetime', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_datetime', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_datetime', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_datetime', 'entity_id', 'catalog_category_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_category_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_datetime', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Category Datetime Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_decimal'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_entity_decimal'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_category_entity_decimal',
            ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_decimal', ['entity_id']),
        ['entity_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_decimal', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_decimal', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_decimal', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_decimal', 'entity_id', 'catalog_category_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_category_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_decimal', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Category Decimal Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_int'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_entity_int'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_category_entity_int',
            ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_int', ['entity_id']),
        ['entity_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_int', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_int', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_int', 'entity_id', 'catalog_category_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_category_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_int', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Category Integer Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_text'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_entity_text'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        '64k',
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_category_entity_text',
            ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_text', ['entity_id']),
        ['entity_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_text', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_text', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_text', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_text', 'entity_id', 'catalog_category_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_category_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_text', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Category Text Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_varchar'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_entity_varchar'))
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity Type ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_category_entity_varchar',
            ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_type_id', 'entity_id', 'attribute_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_varchar', ['entity_id']),
        ['entity_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_varchar', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_entity_varchar', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_varchar', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_varchar', 'entity_id', 'catalog_category_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_category_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_entity_varchar', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Category Varchar Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_product'))
    ->addColumn(
        'category_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Category ID'
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Product ID'
    )
    ->addColumn(
        'position',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'default' => '0'],
        'Position'
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_product', ['product_id']),
        ['product_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_product', 'category_id', 'catalog_category_entity', 'entity_id'),
        'category_id',
        $installer->getTable('catalog_category_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_category_product', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Catalog Product To Category Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product_index'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalog_category_product_index'))
    ->addColumn(
        'category_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Category ID'
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Product ID'
    )
    ->addColumn(
        'position',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => false, 'nullable' => true, 'default' => null],
        'Position'
    )
    ->addColumn(
        'is_parent',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Parent'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'visibility',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Visibility'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_category_product_index',
            ['product_id', 'store_id', 'category_id', 'visibility']
        ),
        ['product_id', 'store_id', 'category_id', 'visibility']
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_category_product_index',
            ['store_id', 'category_id', 'visibility', 'is_parent', 'position']
        ),
        ['store_id', 'category_id', 'visibility', 'is_parent', 'position']
    )
    ->setComment('Catalog Category Product Index');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_compare_item'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_compare_item')
    )
    ->addColumn(
        'catalog_compare_item_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Compare Item ID'
    )
    ->addColumn(
        'visitor_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Visitor ID'
    )
    ->addColumn(
        'customer_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true],
        'Customer ID'
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Product ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true],
        'Store ID'
    )
    ->addIndex(
        $installer->getIdxName('catalog_compare_item', ['product_id']),
        ['product_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_compare_item', ['visitor_id', 'product_id']),
        ['visitor_id', 'product_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_compare_item', ['customer_id', 'product_id']),
        ['customer_id', 'product_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_compare_item', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_compare_item', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id',
        $installer->getTable('customer_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_compare_item', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_compare_item', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Compare Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_website'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_website')
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Product ID'
    )
    ->addColumn(
        'website_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Website ID'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_website', ['website_id']),
        ['website_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_website', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $installer->getTable('store_website'),
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_website', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product To Website Linkage Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_link_type'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_link_type')
    )
    ->addColumn(
        'link_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Link Type ID'
    )
    ->addColumn(
        'code',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        32,
        ['nullable' => true, 'default' => null],
        'Code'
    )
    ->setComment(
        'Catalog Product Link Type Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_link'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_link')
    )
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
        'linked_product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Linked Product ID'
    )
    ->addColumn(
        'link_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Link Type ID'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_link',
            ['link_type_id', 'product_id', 'linked_product_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['link_type_id', 'product_id', 'linked_product_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_link', ['product_id']),
        ['product_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_link', ['linked_product_id']),
        ['linked_product_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_link', 'linked_product_id', 'catalog_product_entity', 'entity_id'),
        'linked_product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_link', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_link', 'link_type_id', 'catalog_product_link_type', 'link_type_id'),
        'link_type_id',
        $installer->getTable('catalog_product_link_type'),
        'link_type_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product To Product Linkage Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_link_attribute'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_link_attribute')
    )
    ->addColumn(
        'product_link_attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Product Link Attribute ID'
    )
    ->addColumn(
        'link_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Link Type ID'
    )
    ->addColumn(
        'product_link_attribute_code',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        32,
        ['nullable' => true, 'default' => null],
        'Product Link Attribute Code'
    )
    ->addColumn(
        'data_type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        32,
        ['nullable' => true, 'default' => null],
        'Data Type'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_link_attribute', ['link_type_id']),
        ['link_type_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_link_attribute',
            'link_type_id',
            'catalog_product_link_type',
            'link_type_id'
        ),
        'link_type_id',
        $installer->getTable('catalog_product_link_type'),
        'link_type_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Link Attribute Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_link_attribute_decimal'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_link_attribute_decimal')
    )
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'product_link_attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true],
        'Product Link Attribute ID'
    )
    ->addColumn(
        'link_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'unsigned' => true],
        'Link ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'default' => '0.0000'],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_link_attribute_decimal', ['link_id']),
        ['link_id']
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_link_attribute_decimal',
            ['product_link_attribute_id', 'link_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['product_link_attribute_id', 'link_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_link_attribute_decimal', 'link_id', 'catalog_product_link', 'link_id'),
        'link_id',
        $installer->getTable('catalog_product_link'),
        'link_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_link_attribute_decimal',
            'product_link_attribute_id',
            'catalog_product_link_attribute',
            'product_link_attribute_id'
        ),
        'product_link_attribute_id',
        $installer->getTable('catalog_product_link_attribute'),
        'product_link_attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Link Decimal Attribute Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_link_attribute_int'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_link_attribute_int')
    )
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'product_link_attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true],
        'Product Link Attribute ID'
    )
    ->addColumn(
        'link_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'unsigned' => true],
        'Link ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'default' => '0'],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_link_attribute_int', ['link_id']),
        ['link_id']
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_link_attribute_int',
            ['product_link_attribute_id', 'link_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['product_link_attribute_id', 'link_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_link_attribute_int', 'link_id', 'catalog_product_link', 'link_id'),
        'link_id',
        $installer->getTable('catalog_product_link'),
        'link_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_link_attribute_int',
            'product_link_attribute_id',
            'catalog_product_link_attribute',
            'product_link_attribute_id'
        ),
        'product_link_attribute_id',
        $installer->getTable('catalog_product_link_attribute'),
        'product_link_attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Link Integer Attribute Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_link_attribute_varchar'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_link_attribute_varchar')
    )
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'product_link_attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Product Link Attribute ID'
    )
    ->addColumn(
        'link_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'unsigned' => true],
        'Link ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_link_attribute_varchar', ['link_id']),
        ['link_id']
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_link_attribute_varchar',
            ['product_link_attribute_id', 'link_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['product_link_attribute_id', 'link_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_link_attribute_varchar', 'link_id', 'catalog_product_link', 'link_id'),
        'link_id',
        $installer->getTable('catalog_product_link'),
        'link_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_link_attribute_varchar',
            'product_link_attribute_id',
            'catalog_product_link_attribute',
            'product_link_attribute_id'
        ),
        'product_link_attribute_id',
        $installer->getTable('catalog_product_link_attribute'),
        'product_link_attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Link Varchar Attribute Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_entity_tier_price'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_entity_tier_price')
    )
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'all_groups',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '1'],
        'Is Applicable To All Customer Groups'
    )
    ->addColumn(
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Customer Group ID'
    )
    ->addColumn(
        'qty',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'default' => '1.0000'],
        'QTY'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'default' => '0.0000'],
        'Value'
    )
    ->addColumn(
        'website_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Website ID'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_tier_price',
            ['entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_tier_price', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_tier_price', ['website_id']),
        ['website_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_entity_tier_price',
            'customer_group_id',
            'customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $installer->getTable('customer_group'),
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_tier_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_tier_price', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $installer->getTable('store_website'),
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Tier Price Attribute Backend Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_entity_media_gallery'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_entity_media_gallery')
    )
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Attribute ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_media_gallery', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_media_gallery', ['entity_id']),
        ['entity_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_media_gallery', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_entity_media_gallery',
            'entity_id',
            'catalog_product_entity',
            'entity_id'
        ),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Media Gallery Attribute Backend Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_entity_media_gallery_value'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_entity_media_gallery_value')
    )
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Value ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'label',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'Label'
    )
    ->addColumn(
        'position',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true],
        'Position'
    )
    ->addColumn(
        'disabled',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Disabled'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_media_gallery_value', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_media_gallery_value', ['entity_id']),
        ['entity_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_entity_media_gallery_value',
            'value_id',
            'catalog_product_entity_media_gallery',
            'value_id'
        ),
        'value_id',
        $installer->getTable('catalog_product_entity_media_gallery'),
        'value_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_media_gallery_value', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_entity_media_gallery_value',
            'entity_id',
            'catalog_product_entity',
            'entity_id'
        ),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Media Gallery Attribute Value Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_option'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_option')
    )
    ->addColumn(
        'option_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Option ID'
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Product ID'
    )
    ->addColumn(
        'type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        50,
        ['nullable' => true, 'default' => null],
        'Type'
    )
    ->addColumn(
        'is_require',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['nullable' => false, 'default' => '1'],
        'Is Required'
    )
    ->addColumn(
        'sku',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        64,
        [],
        'SKU'
    )
    ->addColumn(
        'max_characters',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true],
        'Max Characters'
    )
    ->addColumn(
        'file_extension',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        50,
        [],
        'File Extension'
    )
    ->addColumn(
        'image_size_x',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true],
        'Image Size X'
    )
    ->addColumn(
        'image_size_y',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true],
        'Image Size Y'
    )
    ->addColumn(
        'sort_order',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Sort Order'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_option', ['product_id']),
        ['product_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Option Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_option_price'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_option_price')
    )
    ->addColumn(
        'option_price_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Option Price ID'
    )
    ->addColumn(
        'option_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Option ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'default' => '0.0000'],
        'Price'
    )
    ->addColumn(
        'price_type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        7,
        ['nullable' => false, 'default' => 'fixed'],
        'Price Type'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_option_price',
            ['option_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['option_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_option_price', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option_price', 'option_id', 'catalog_product_option', 'option_id'),
        'option_id',
        $installer->getTable('catalog_product_option'),
        'option_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option_price', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Option Price Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_option_title'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_option_title')
    )
    ->addColumn(
        'option_title_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Option Title ID'
    )
    ->addColumn(
        'option_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Option ID'
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
        ['nullable' => true, 'default' => null],
        'Title'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_option_title',
            ['option_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['option_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_option_title', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option_title', 'option_id', 'catalog_product_option', 'option_id'),
        'option_id',
        $installer->getTable('catalog_product_option'),
        'option_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option_title', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Option Title Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_option_type_value'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_option_type_value')
    )
    ->addColumn(
        'option_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Option Type ID'
    )
    ->addColumn(
        'option_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Option ID'
    )
    ->addColumn(
        'sku',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        64,
        [],
        'SKU'
    )
    ->addColumn(
        'sort_order',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Sort Order'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_option_type_value', ['option_id']),
        ['option_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option_type_value', 'option_id', 'catalog_product_option', 'option_id'),
        'option_id',
        $installer->getTable('catalog_product_option'),
        'option_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Option Type Value Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_option_type_price'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_option_type_price')
    )
    ->addColumn(
        'option_type_price_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Option Type Price ID'
    )
    ->addColumn(
        'option_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Option Type ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'default' => '0.0000'],
        'Price'
    )
    ->addColumn(
        'price_type',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        7,
        ['nullable' => false, 'default' => 'fixed'],
        'Price Type'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_option_type_price',
            ['option_type_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['option_type_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_option_type_price', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_option_type_price',
            'option_type_id',
            'catalog_product_option_type_value',
            'option_type_id'
        ),
        'option_type_id',
        $installer->getTable('catalog_product_option_type_value'),
        'option_type_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option_type_price', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Option Type Price Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_option_type_title'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_option_type_title')
    )
    ->addColumn(
        'option_type_title_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Option Type Title ID'
    )
    ->addColumn(
        'option_type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Option Type ID'
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
        ['nullable' => true, 'default' => null],
        'Title'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_option_type_title',
            ['option_type_id', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['option_type_id', 'store_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_option_type_title', ['store_id']),
        ['store_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_option_type_title',
            'option_type_id',
            'catalog_product_option_type_value',
            'option_type_id'
        ),
        'option_type_id',
        $installer->getTable('catalog_product_option_type_value'),
        'option_type_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_option_type_title', 'store_id', 'store', 'store_id'),
        'store_id',
        $installer->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Option Type Title Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_eav_attribute'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_eav_attribute')
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Attribute ID'
    )
    ->addColumn(
        'frontend_input_renderer',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        [],
        'Frontend Input Renderer'
    )
    ->addColumn(
        'is_global',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '1'],
        'Is Global'
    )
    ->addColumn(
        'is_visible',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '1'],
        'Is Visible'
    )
    ->addColumn(
        'is_searchable',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Searchable'
    )
    ->addColumn(
        'is_filterable',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Filterable'
    )
    ->addColumn(
        'is_comparable',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Comparable'
    )
    ->addColumn(
        'is_visible_on_front',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Visible On Front'
    )
    ->addColumn(
        'is_html_allowed_on_front',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is HTML Allowed On Front'
    )
    ->addColumn(
        'is_used_for_price_rules',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Used For Price Rules'
    )
    ->addColumn(
        'is_filterable_in_search',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Filterable In Search'
    )
    ->addColumn(
        'used_in_product_listing',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Used In Product Listing'
    )
    ->addColumn(
        'used_for_sort_by',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Used For Sorting'
    )
    ->addColumn(
        'apply_to',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        ['nullable' => true],
        'Apply To'
    )
    ->addColumn(
        'is_visible_in_advanced_search',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Visible In Advanced Search'
    )
    ->addColumn(
        'position',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'default' => '0'],
        'Position'
    )
    ->addColumn(
        'is_wysiwyg_enabled',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is WYSIWYG Enabled'
    )
    ->addColumn(
        'is_used_for_promo_rules',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Used For Promo Rules'
    )
    ->addColumn(
        'is_required_in_admin_store',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Required In Admin Store'
    )
    ->addIndex(
        $installer->getIdxName('catalog_eav_attribute', ['used_for_sort_by']),
        ['used_for_sort_by']
    )
    ->addIndex(
        $installer->getIdxName('catalog_eav_attribute', ['used_in_product_listing']),
        ['used_in_product_listing']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_eav_attribute', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $installer->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog EAV Attribute Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_relation'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_relation')
    )
    ->addColumn(
        'parent_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Parent ID'
    )
    ->addColumn(
        'child_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Child ID'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_relation', ['child_id']),
        ['child_id']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_relation', 'child_id', 'catalog_product_entity', 'entity_id'),
        'child_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_relation', 'parent_id', 'catalog_product_entity', 'entity_id'),
        'parent_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Relation Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_eav'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_eav')
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Store ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav', ['value']),
        ['value']
    )
    ->setComment(
        'Catalog Product EAV Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_eav_decimal'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_eav_decimal')
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Store ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'primary' => false],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal', ['value']),
        ['value']
    )
    ->setComment(
        'Catalog Product EAV Decimal Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price')
    )
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
        'tax_class_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'default' => '0'],
        'Tax Class ID'
    )
    ->addColumn(
        'price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Price'
    )
    ->addColumn(
        'final_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Final Price'
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price', ['min_price']),
        ['min_price']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price', ['website_id', 'customer_group_id', 'min_price']),
        ['website_id', 'customer_group_id', 'min_price']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_index_price',
            'customer_group_id',
            'customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $installer->getTable('customer_group'),
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_index_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_index_price', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $installer->getTable('store_website'),
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Price Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_tier_price'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_tier_price')
    )
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
        [],
        'Min Price'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_tier_price', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_tier_price', ['website_id']),
        ['website_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_index_tier_price',
            'customer_group_id',
            'customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $installer->getTable('customer_group'),
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_index_tier_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_index_tier_price', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $installer->getTable('store_website'),
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Tier Price Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_website'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_website')
    )
    ->addColumn(
        'website_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Website ID'
    )
    ->addColumn(
        'website_date',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
        null,
        [],
        'Website Date'
    )
    ->addColumn(
        'rate',
        \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
        null,
        ['default' => '1.0000'],
        'Rate'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_website', ['website_date']),
        ['website_date']
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_index_website', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $installer->getTable('store_website'),
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Website Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_agr_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_cfg_opt_agr_idx')
    )
    ->addColumn(
        'parent_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Parent ID'
    )
    ->addColumn(
        'child_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Child ID'
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
    ->addColumn(
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setComment(
        'Catalog Product Price Indexer Config Option Aggregate Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_agr_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_cfg_opt_agr_tmp')
    )
    ->addColumn(
        'parent_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Parent ID'
    )
    ->addColumn(
        'child_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Child ID'
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
    ->addColumn(
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product Price Indexer Config Option Aggregate Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_cfg_opt_idx')
    )
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setComment(
        'Catalog Product Price Indexer Config Option Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_cfg_opt_tmp')
    )
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product Price Indexer Config Option Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_final_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_final_idx')
    )
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
        'tax_class_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'default' => '0'],
        'Tax Class ID'
    )
    ->addColumn(
        'orig_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Original Price'
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
    ->addColumn(
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->addColumn(
        'base_group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Base Group Price'
    )
    ->setComment(
        'Catalog Product Price Indexer Final Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_final_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_final_tmp')
    )
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
        'tax_class_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'default' => '0'],
        'Tax Class ID'
    )
    ->addColumn(
        'orig_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Original Price'
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
    ->addColumn(
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->addColumn(
        'base_group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Base Group Price'
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product Price Indexer Final Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_opt_idx')
    )
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setComment(
        'Catalog Product Price Indexer Option Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_opt_tmp')
    )
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product Price Indexer Option Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_agr_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_opt_agr_idx')
    )
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
        'option_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Option ID'
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setComment(
        'Catalog Product Price Indexer Option Aggregate Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_agr_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_opt_agr_tmp')
    )
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
        'option_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
        'Option ID'
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product Price Indexer Option Aggregate Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_eav_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_eav_idx')
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Store ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_idx', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_idx', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_idx', ['value']),
        ['value']
    )
    ->setComment(
        'Catalog Product EAV Indexer Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_eav_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_eav_tmp')
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Store ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_tmp', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_tmp', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_tmp', ['value']),
        ['value']
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product EAV Indexer Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_eav_decimal_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_eav_decimal_idx')
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Store ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'primary' => true],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal_idx', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal_idx', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal_idx', ['value']),
        ['value']
    )
    ->setComment(
        'Catalog Product EAV Decimal Indexer Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_eav_decimal_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_eav_decimal_tmp')
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Entity ID'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Attribute ID'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true],
        'Store ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'primary' => false],
        'Value'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal_tmp', ['attribute_id']),
        ['attribute_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal_tmp', ['store_id']),
        ['store_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_eav_decimal_tmp', ['value']),
        ['value']
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product EAV Decimal Indexer Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_idx'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_idx')
    )
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
        'tax_class_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'default' => '0'],
        'Tax Class ID'
    )
    ->addColumn(
        'price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Price'
    )
    ->addColumn(
        'final_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Final Price'
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price_idx', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price_idx', ['website_id']),
        ['website_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price_idx', ['min_price']),
        ['min_price']
    )
    ->setComment(
        'Catalog Product Price Indexer Index Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_price_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_price_tmp')
    )
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
        'tax_class_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'default' => '0'],
        'Tax Class ID'
    )
    ->addColumn(
        'price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Price'
    )
    ->addColumn(
        'final_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Final Price'
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
        'group_price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Group price'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price_tmp', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price_tmp', ['website_id']),
        ['website_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_price_tmp', ['min_price']),
        ['min_price']
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Product Price Indexer Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_category_product_index_tmp'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_category_product_index_tmp')
    )
    ->addColumn(
        'category_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Category ID'
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Product ID'
    )
    ->addColumn(
        'position',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'default' => '0'],
        'Position'
    )
    ->addColumn(
        'is_parent',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Is Parent'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Store ID'
    )
    ->addColumn(
        'visibility',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Visibility'
    )
    ->addIndex(
        $installer->getIdxName('catalog_category_product_index_tmp', ['product_id', 'category_id', 'store_id']),
        ['product_id', 'category_id', 'store_id']
    )
    ->setOption(
        'type',
        \Magento\Framework\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
    )
    ->setComment(
        'Catalog Category Product Indexer Temp Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_entity_group_price'
 */
$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_entity_group_price')
    )
    ->addColumn(
        'value_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'primary' => true],
        'Value ID'
    )
    ->addColumn(
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Entity ID'
    )
    ->addColumn(
        'all_groups',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '1'],
        'Is Applicable To All Customer Groups'
    )
    ->addColumn(
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Customer Group ID'
    )
    ->addColumn(
        'value',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        ['nullable' => false, 'default' => '0.0000'],
        'Value'
    )
    ->addColumn(
        'website_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Website ID'
    )
    ->addIndex(
        $installer->getIdxName(
            'catalog_product_entity_group_price',
            ['entity_id', 'all_groups', 'customer_group_id', 'website_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['entity_id', 'all_groups', 'customer_group_id', 'website_id'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_group_price', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_entity_group_price', ['website_id']),
        ['website_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_entity_group_price',
            'customer_group_id',
            'customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $installer->getTable('customer_group'),
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_group_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_entity_group_price', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $installer->getTable('store_website'),
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Group Price Attribute Backend Table'
    );
$installer->getConnection()
    ->createTable($table);

/**
 * Create table 'catalog_product_index_group_price'
 */

$table = $installer->getConnection()
    ->newTable(
        $installer->getTable('catalog_product_index_group_price')
    )
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
        'price',
        \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        '12,4',
        [],
        'Min Price'
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_group_price', ['customer_group_id']),
        ['customer_group_id']
    )
    ->addIndex(
        $installer->getIdxName('catalog_product_index_group_price', ['website_id']),
        ['website_id']
    )
    ->addForeignKey(
        $installer->getFkName(
            'catalog_product_index_group_price',
            'customer_group_id',
            'customer_group',
            'customer_group_id'
        ),
        'customer_group_id',
        $installer->getTable('customer_group'),
        'customer_group_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_index_group_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
        'entity_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('catalog_product_index_group_price', 'website_id', 'store_website', 'website_id'),
        'website_id',
        $installer->getTable('store_website'),
        'website_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment(
        'Catalog Product Group Price Index Table'
    );
$installer->getConnection()
    ->createTable($table);

$installer->endSetup();
