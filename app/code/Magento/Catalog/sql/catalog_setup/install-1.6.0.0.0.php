<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'catalog_product_entity'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_set_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute Set ID'
)->addColumn(
    'type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => false, 'default' => 'simple'),
    'Type ID'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array(),
    'SKU'
)->addColumn(
    'has_options',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '0'),
    'Has Options'
)->addColumn(
    'required_options',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Required Options'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Creation Time'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Update Time'
)->addIndex(
    $installer->getIdxName('catalog_product_entity', array('entity_type_id')),
    array('entity_type_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity', array('attribute_set_id')),
    array('attribute_set_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity', array('sku')),
    array('sku')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity', 'attribute_set_id', 'eav_attribute_set', 'attribute_set_id'),
    'attribute_set_id',
    $installer->getTable('eav_attribute_set'),
    'attribute_set_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity', 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
    'entity_type_id',
    $installer->getTable('eav_entity_type'),
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Table'
);
$installer->getConnection()->createTable($table);



/**
 * Create table 'catalog_product_entity_datetime'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_datetime')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
    null,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_entity_datetime',
        array('entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_entity_datetime', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_datetime', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_datetime', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_datetime', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_datetime', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Datetime Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_decimal'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_decimal')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_entity_decimal',
        array('entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_entity_decimal', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_decimal', array('attribute_id')),
    array('attribute_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_decimal', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_decimal', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_decimal', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Decimal Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_int'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_int')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_entity_int',
        array('entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_entity_int', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_int', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_int', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_int', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Integer Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_text'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_text')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_entity_text',
        array('entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_entity_text', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_text', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_text', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_text', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_text', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Text Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_varchar'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_varchar')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_entity_varchar',
        array('entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_entity_varchar', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_varchar', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_varchar', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_varchar', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_varchar', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Varchar Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_gallery'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_gallery')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Position'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true, 'default' => null),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_entity_gallery',
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_entity_gallery', array('entity_id')),
    array('entity_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_gallery', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_gallery', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_gallery', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_gallery', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_gallery', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Gallery Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_entity')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_set_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attriute Set ID'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Parent Category ID'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Creation Time'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Update Time'
)->addColumn(
    'path',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Tree Path'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false),
    'Position'
)->addColumn(
    'level',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Tree Level'
)->addColumn(
    'children_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false),
    'Child Count'
)->addIndex(
    $installer->getIdxName('catalog_category_entity', array('level')),
    array('level')
)->setComment(
    'Catalog Category Table'
);
$installer->getConnection()->createTable($table);


/**
 * Create table 'catalog_category_entity_datetime'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_entity_datetime')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
    null,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_category_entity_datetime',
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_category_entity_datetime', array('entity_id')),
    array('entity_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_datetime', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_datetime', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_datetime', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_datetime', 'entity_id', 'catalog_category_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_datetime', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Category Datetime Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_decimal'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_entity_decimal')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_category_entity_decimal',
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_category_entity_decimal', array('entity_id')),
    array('entity_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_decimal', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_decimal', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_decimal', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_decimal', 'entity_id', 'catalog_category_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_decimal', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Category Decimal Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_int'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_entity_int')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_category_entity_int',
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_category_entity_int', array('entity_id')),
    array('entity_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_int', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_int', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_int', 'entity_id', 'catalog_category_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_int', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Category Integer Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_text'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_entity_text')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_category_entity_text',
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_category_entity_text', array('entity_id')),
    array('entity_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_text', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_text', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_text', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_text', 'entity_id', 'catalog_category_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_text', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Category Text Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_entity_varchar'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_entity_varchar')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity Type ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName(
        'catalog_category_entity_varchar',
        array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_type_id', 'entity_id', 'attribute_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_category_entity_varchar', array('entity_id')),
    array('entity_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_varchar', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_category_entity_varchar', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_varchar', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_varchar', 'entity_id', 'catalog_category_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_entity_varchar', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Category Varchar Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_product')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Category ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Product ID'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Position'
    /*    ->addIndex($installer->getIdxName('catalog_category_product', array('category_id')),
    array('category_id'))*/
)->addIndex(
    $installer->getIdxName('catalog_category_product', array('product_id')),
    array('product_id')
)->addForeignKey(
    $installer->getFkName('catalog_category_product', 'category_id', 'catalog_category_entity', 'entity_id'),
    'category_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_product', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product To Category Linkage Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product_index'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_product_index')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Category ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Product ID'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Position'
)->addColumn(
    'is_parent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Parent'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Store ID'
)->addColumn(
    'visibility',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Visibility'
)->addIndex(
    $installer->getIdxName(
        'catalog_category_product_index',
        array('product_id', 'store_id', 'category_id', 'visibility')
    ),
    array('product_id', 'store_id', 'category_id', 'visibility')
)->addIndex(
    $installer->getIdxName(
        'catalog_category_product_index',
        array('store_id', 'category_id', 'visibility', 'is_parent', 'position')
    ),
    array('store_id', 'category_id', 'visibility', 'is_parent', 'position')
)->addForeignKey(
    $installer->getFkName('catalog_category_product_index', 'category_id', 'catalog_category_entity', 'entity_id'),
    'category_id',
    $installer->getTable('catalog_category_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_product_index', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_category_product_index', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Category Product Index'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_compare_item'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_compare_item')
)->addColumn(
    'catalog_compare_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Compare Item ID'
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Visitor ID'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Customer ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Store ID'
)->addIndex(
    $installer->getIdxName('catalog_compare_item', array('product_id')),
    array('product_id')
)->addIndex(
    $installer->getIdxName('catalog_compare_item', array('visitor_id', 'product_id')),
    array('visitor_id', 'product_id')
)->addIndex(
    $installer->getIdxName('catalog_compare_item', array('customer_id', 'product_id')),
    array('customer_id', 'product_id')
)->addIndex(
    $installer->getIdxName('catalog_compare_item', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_compare_item', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_compare_item', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_compare_item', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Compare Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_website'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_website')
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Product ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addIndex(
    $installer->getIdxName('catalog_product_website', array('website_id')),
    array('website_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_website', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_website', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product To Website Linkage Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_enabled_index'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_enabled_index')
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Product ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Store ID'
)->addColumn(
    'visibility',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Visibility'
)->addIndex(
    $installer->getIdxName('catalog_product_enabled_index', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_enabled_index', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_enabled_index', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Visibility Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_link_type'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_link_type')
)->addColumn(
    'link_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Link Type ID'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => true, 'default' => null),
    'Code'
)->setComment(
    'Catalog Product Link Type Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_link'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_link')
)->addColumn(
    'link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Link ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'linked_product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Linked Product ID'
)->addColumn(
    'link_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Link Type ID'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_link',
        array('link_type_id', 'product_id', 'linked_product_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('link_type_id', 'product_id', 'linked_product_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_link', array('product_id')),
    array('product_id')
)->addIndex(
    $installer->getIdxName('catalog_product_link', array('linked_product_id')),
    array('linked_product_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_link', 'linked_product_id', 'catalog_product_entity', 'entity_id'),
    'linked_product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_link', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_link', 'link_type_id', 'catalog_product_link_type', 'link_type_id'),
    'link_type_id',
    $installer->getTable('catalog_product_link_type'),
    'link_type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product To Product Linkage Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_link_attribute'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_link_attribute')
)->addColumn(
    'product_link_attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Product Link Attribute ID'
)->addColumn(
    'link_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Link Type ID'
)->addColumn(
    'product_link_attribute_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => true, 'default' => null),
    'Product Link Attribute Code'
)->addColumn(
    'data_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => true, 'default' => null),
    'Data Type'
)->addIndex(
    $installer->getIdxName('catalog_product_link_attribute', array('link_type_id')),
    array('link_type_id')
)->addForeignKey(
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
)->setComment(
    'Catalog Product Link Attribute Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_link_attribute_decimal'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_link_attribute_decimal')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'product_link_attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Product Link Attribute ID'
)->addColumn(
    'link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Link ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'default' => '0.0000'),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_link_attribute_decimal', array('link_id')),
    array('link_id')
)->addIndex(
    $installer->getIdxName(
        'catalog_product_link_attribute_decimal',
        array('product_link_attribute_id', 'link_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('product_link_attribute_id', 'link_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addForeignKey(
    $installer->getFkName('catalog_product_link_attribute_decimal', 'link_id', 'catalog_product_link', 'link_id'),
    'link_id',
    $installer->getTable('catalog_product_link'),
    'link_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
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
)->setComment(
    'Catalog Product Link Decimal Attribute Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_link_attribute_int'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_link_attribute_int')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'product_link_attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Product Link Attribute ID'
)->addColumn(
    'link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Link ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_link_attribute_int', array('link_id')),
    array('link_id')
)->addIndex(
    $installer->getIdxName(
        'catalog_product_link_attribute_int',
        array('product_link_attribute_id', 'link_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('product_link_attribute_id', 'link_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addForeignKey(
    $installer->getFkName('catalog_product_link_attribute_int', 'link_id', 'catalog_product_link', 'link_id'),
    'link_id',
    $installer->getTable('catalog_product_link'),
    'link_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
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
)->setComment(
    'Catalog Product Link Integer Attribute Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_link_attribute_varchar'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_link_attribute_varchar')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'product_link_attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product Link Attribute ID'
)->addColumn(
    'link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Link ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_link_attribute_varchar', array('link_id')),
    array('link_id')
)->addIndex(
    $installer->getIdxName(
        'catalog_product_link_attribute_varchar',
        array('product_link_attribute_id', 'link_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('product_link_attribute_id', 'link_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addForeignKey(
    $installer->getFkName('catalog_product_link_attribute_varchar', 'link_id', 'catalog_product_link', 'link_id'),
    'link_id',
    $installer->getTable('catalog_product_link'),
    'link_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
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
)->setComment(
    'Catalog Product Link Varchar Attribute Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_tier_price'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_tier_price')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'all_groups',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '1'),
    'Is Applicable To All Customer Groups'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Customer Group ID'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'default' => '1.0000'),
    'QTY'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'default' => '0.0000'),
    'Value'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Website ID'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_entity_tier_price',
        array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_entity_tier_price', array('customer_group_id')),
    array('customer_group_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_tier_price', array('website_id')),
    array('website_id')
)->addForeignKey(
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
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_tier_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_tier_price', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Tier Price Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_media_gallery'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_media_gallery')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Value ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Attribute ID'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Entity ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_entity_media_gallery', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_entity_media_gallery', array('entity_id')),
    array('entity_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_media_gallery', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_media_gallery', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Media Gallery Attribute Backend Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_entity_media_gallery_value'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_entity_media_gallery_value')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Value ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Store ID'
)->addColumn(
    'label',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Label'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Position'
)->addColumn(
    'disabled',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Disabled'
)->addIndex(
    $installer->getIdxName('catalog_product_entity_media_gallery_value', array('store_id')),
    array('store_id')
)->addForeignKey(
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
)->addForeignKey(
    $installer->getFkName('catalog_product_entity_media_gallery_value', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Media Gallery Attribute Value Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_option'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_option')
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Option ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    array('nullable' => true, 'default' => null),
    'Type'
)->addColumn(
    'is_require',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'default' => '1'),
    'Is Required'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array(),
    'SKU'
)->addColumn(
    'max_characters',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Max Characters'
)->addColumn(
    'file_extension',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    array(),
    'File Extension'
)->addColumn(
    'image_size_x',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Image Size X'
)->addColumn(
    'image_size_y',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Image Size Y'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Sort Order'
)->addIndex(
    $installer->getIdxName('catalog_product_option', array('product_id')),
    array('product_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_option', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Option Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_option_price'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_option_price')
)->addColumn(
    'option_price_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Option Price ID'
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Option ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'default' => '0.0000'),
    'Price'
)->addColumn(
    'price_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    7,
    array('nullable' => false, 'default' => 'fixed'),
    'Price Type'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_option_price',
        array('option_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('option_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_option_price', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_option_price', 'option_id', 'catalog_product_option', 'option_id'),
    'option_id',
    $installer->getTable('catalog_product_option'),
    'option_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_option_price', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Option Price Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_option_title'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_option_title')
)->addColumn(
    'option_title_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Option Title ID'
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Option ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true, 'default' => null),
    'Title'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_option_title',
        array('option_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('option_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_option_title', array('store_id')),
    array('store_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_option_title', 'option_id', 'catalog_product_option', 'option_id'),
    'option_id',
    $installer->getTable('catalog_product_option'),
    'option_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_option_title', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Option Title Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_option_type_value'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_option_type_value')
)->addColumn(
    'option_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Option Type ID'
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Option ID'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array(),
    'SKU'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Sort Order'
)->addIndex(
    $installer->getIdxName('catalog_product_option_type_value', array('option_id')),
    array('option_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_option_type_value', 'option_id', 'catalog_product_option', 'option_id'),
    'option_id',
    $installer->getTable('catalog_product_option'),
    'option_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Option Type Value Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_option_type_price'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_option_type_price')
)->addColumn(
    'option_type_price_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Option Type Price ID'
)->addColumn(
    'option_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Option Type ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'default' => '0.0000'),
    'Price'
)->addColumn(
    'price_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    7,
    array('nullable' => false, 'default' => 'fixed'),
    'Price Type'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_option_type_price',
        array('option_type_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('option_type_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_option_type_price', array('store_id')),
    array('store_id')
)->addForeignKey(
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
)->addForeignKey(
    $installer->getFkName('catalog_product_option_type_price', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Option Type Price Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_option_type_title'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_option_type_title')
)->addColumn(
    'option_type_title_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Option Type Title ID'
)->addColumn(
    'option_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Option Type ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true, 'default' => null),
    'Title'
)->addIndex(
    $installer->getIdxName(
        'catalog_product_option_type_title',
        array('option_type_id', 'store_id'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('option_type_id', 'store_id'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addIndex(
    $installer->getIdxName('catalog_product_option_type_title', array('store_id')),
    array('store_id')
)->addForeignKey(
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
)->addForeignKey(
    $installer->getFkName('catalog_product_option_type_title', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Option Type Title Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_eav_attribute'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_eav_attribute')
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Attribute ID'
)->addColumn(
    'frontend_input_renderer',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Frontend Input Renderer'
)->addColumn(
    'is_global',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '1'),
    'Is Global'
)->addColumn(
    'is_visible',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '1'),
    'Is Visible'
)->addColumn(
    'is_searchable',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Searchable'
)->addColumn(
    'is_filterable',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Filterable'
)->addColumn(
    'is_comparable',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Comparable'
)->addColumn(
    'is_visible_on_front',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Visible On Front'
)->addColumn(
    'is_html_allowed_on_front',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is HTML Allowed On Front'
)->addColumn(
    'is_used_for_price_rules',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Used For Price Rules'
)->addColumn(
    'is_filterable_in_search',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Filterable In Search'
)->addColumn(
    'used_in_product_listing',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Used In Product Listing'
)->addColumn(
    'used_for_sort_by',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Used For Sorting'
)->addColumn(
    'apply_to',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true),
    'Apply To'
)->addColumn(
    'is_visible_in_advanced_search',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Visible In Advanced Search'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Position'
)->addColumn(
    'is_wysiwyg_enabled',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is WYSIWYG Enabled'
)->addColumn(
    'is_used_for_promo_rules',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Used For Promo Rules'
)->addIndex(
    $installer->getIdxName('catalog_eav_attribute', array('used_for_sort_by')),
    array('used_for_sort_by')
)->addIndex(
    $installer->getIdxName('catalog_eav_attribute', array('used_in_product_listing')),
    array('used_in_product_listing')
)->addForeignKey(
    $installer->getFkName('catalog_eav_attribute', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog EAV Attribute Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_relation'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_relation')
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Parent ID'
)->addColumn(
    'child_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Child ID'
)->addIndex(
    $installer->getIdxName('catalog_product_relation', array('child_id')),
    array('child_id')
)->addForeignKey(
    $installer->getFkName('catalog_product_relation', 'child_id', 'catalog_product_entity', 'entity_id'),
    'child_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_relation', 'parent_id', 'catalog_product_entity', 'entity_id'),
    'parent_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Relation Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_eav'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_eav')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav', array('value')),
    array('value')
)->addForeignKey(
    $installer->getFkName('catalog_product_index_eav', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_index_eav', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_index_eav', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product EAV Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_eav_decimal'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_eav_decimal')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'primary' => true),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal', array('value')),
    array('value')
)->addForeignKey(
    $installer->getFkName('catalog_product_index_eav_decimal', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $installer->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_index_eav_decimal', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_index_eav_decimal', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product EAV Decimal Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Tax Class ID'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Price'
)->addColumn(
    'final_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Final Price'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->addIndex(
    $installer->getIdxName('catalog_product_index_price', array('customer_group_id')),
    array('customer_group_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_price', array('min_price')),
    array('min_price')
)->addForeignKey(
    $installer->getFkName('catalog_product_index_price', 'customer_group_id', 'customer_group', 'customer_group_id'),
    'customer_group_id',
    $installer->getTable('customer_group'),
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_index_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_index_price', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Price Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_tier_price'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_tier_price')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addIndex(
    $installer->getIdxName('catalog_product_index_tier_price', array('customer_group_id')),
    array('customer_group_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_tier_price', array('website_id')),
    array('website_id')
)->addForeignKey(
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
)->addForeignKey(
    $installer->getFkName('catalog_product_index_tier_price', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('catalog_product_index_tier_price', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Tier Price Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_website'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_website')
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'website_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    array(),
    'Website Date'
)->addColumn(
    'rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
    null,
    array('default' => '1.0000'),
    'Rate'
)->addIndex(
    $installer->getIdxName('catalog_product_index_website', array('website_date')),
    array('website_date')
)->addForeignKey(
    $installer->getFkName('catalog_product_index_website', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Catalog Product Website Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_agr_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_cfg_opt_agr_idx')
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Parent ID'
)->addColumn(
    'child_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Child ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Config Option Aggregate Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_agr_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_cfg_opt_agr_tmp')
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Parent ID'
)->addColumn(
    'child_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Child ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Config Option Aggregate Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_cfg_opt_idx')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Config Option Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_cfg_opt_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_cfg_opt_tmp')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Config Option Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_final_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_final_idx')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Tax Class ID'
)->addColumn(
    'orig_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Original Price'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Price'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->addColumn(
    'base_tier',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Base Tier'
)->setComment(
    'Catalog Product Price Indexer Final Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_final_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_final_tmp')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Tax Class ID'
)->addColumn(
    'orig_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Original Price'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Price'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->addColumn(
    'base_tier',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Base Tier'
)->setComment(
    'Catalog Product Price Indexer Final Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_opt_idx')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Option Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_opt_tmp')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Option Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_agr_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_opt_agr_idx')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Option ID'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Option Aggregate Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_opt_agr_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_opt_agr_tmp')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Option ID'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->setComment(
    'Catalog Product Price Indexer Option Aggregate Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_eav_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_eav_idx')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_idx', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_idx', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_idx', array('value')),
    array('value')
)->setComment(
    'Catalog Product EAV Indexer Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_eav_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_eav_tmp')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_tmp', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_tmp', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_tmp', array('value')),
    array('value')
)->setComment(
    'Catalog Product EAV Indexer Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_eav_decimal_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_eav_decimal_idx')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'primary' => true),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal_idx', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal_idx', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal_idx', array('value')),
    array('value')
)->setComment(
    'Catalog Product EAV Decimal Indexer Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_eav_decimal_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_eav_decimal_tmp')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Attribute ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Store ID'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'primary' => true),
    'Value'
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal_tmp', array('attribute_id')),
    array('attribute_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal_tmp', array('store_id')),
    array('store_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_eav_decimal_tmp', array('value')),
    array('value')
)->setComment(
    'Catalog Product EAV Decimal Indexer Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_idx')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Tax Class ID'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Price'
)->addColumn(
    'final_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Final Price'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->addIndex(
    $installer->getIdxName('catalog_product_index_price_idx', array('customer_group_id')),
    array('customer_group_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_price_idx', array('website_id')),
    array('website_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_price_idx', array('min_price')),
    array('min_price')
)->setComment(
    'Catalog Product Price Indexer Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_product_index_price_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_product_index_price_tmp')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Entity ID'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Customer Group ID'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Website ID'
)->addColumn(
    'tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'default' => '0'),
    'Tax Class ID'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Price'
)->addColumn(
    'final_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Final Price'
)->addColumn(
    'min_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Min Price'
)->addColumn(
    'max_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Max Price'
)->addColumn(
    'tier_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array(),
    'Tier Price'
)->addIndex(
    $installer->getIdxName('catalog_product_index_price_tmp', array('customer_group_id')),
    array('customer_group_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_price_tmp', array('website_id')),
    array('website_id')
)->addIndex(
    $installer->getIdxName('catalog_product_index_price_tmp', array('min_price')),
    array('min_price')
)->setComment(
    'Catalog Product Price Indexer Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product_index_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_product_index_idx')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Category ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Position'
)->addColumn(
    'is_parent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Parent'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'visibility',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Visibility'
)->addIndex(
    $installer->getIdxName('catalog_category_product_index_idx', array('product_id', 'category_id', 'store_id')),
    array('product_id', 'category_id', 'store_id')
)->setComment(
    'Catalog Category Product Indexer Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product_index_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_product_index_tmp')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Category ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Position'
)->addColumn(
    'is_parent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Is Parent'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Store ID'
)->addColumn(
    'visibility',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Visibility'
)->setComment(
    'Catalog Category Product Indexer Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product_index_enbl_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_product_index_enbl_idx')
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'visibility',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Visibility'
)->addIndex(
    $installer->getIdxName('catalog_category_product_index_enbl_idx', array('product_id')),
    array('product_id')
)->setComment(
    'Catalog Category Product Enabled Indexer Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_product_index_enbl_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_product_index_enbl_tmp')
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'visibility',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Visibility'
)->addIndex(
    $installer->getIdxName('catalog_category_product_index_enbl_tmp', array('product_id')),
    array('product_id')
)->setComment(
    'Catalog Category Product Enabled Indexer Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_anc_categs_index_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_anc_categs_index_idx')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Category ID'
)->addColumn(
    'path',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true, 'default' => null),
    'Path'
)->addIndex(
    $installer->getIdxName('catalog_category_anc_categs_index_idx', array('category_id')),
    array('category_id')
)->setComment(
    'Catalog Category Anchor Indexer Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_anc_categs_index_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_anc_categs_index_tmp')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Category ID'
)->addColumn(
    'path',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true, 'default' => null),
    'Path'
)->addIndex(
    $installer->getIdxName('catalog_category_anc_categs_index_tmp', array('category_id')),
    array('category_id')
)->setComment(
    'Catalog Category Anchor Indexer Temp Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_anc_products_index_idx'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_anc_products_index_idx')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Category ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Position'
)->setComment(
    'Catalog Category Anchor Product Indexer Index Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalog_category_anc_products_index_tmp'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('catalog_category_anc_products_index_tmp')
)->addColumn(
    'category_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Category ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->setComment(
    'Catalog Category Anchor Product Indexer Temp Table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
