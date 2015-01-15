<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * GoogleShopping install
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
/** @var $installer \Magento\Framework\Module\Setup */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$table = $connection->newTable($this->getTable('googleshopping_types'))
    ->addColumn(
        'type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Type ID'
    )
    ->addColumn(
        'attribute_set_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Attribute Set Id'
    )
    ->addColumn(
        'target_country',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        2,
        ['nullable' => false, 'default' => 'US'],
        'Target country'
    )
    ->addColumn(
        'category',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        40,
        [],
        'Google product category'
    )
    ->addForeignKey(
        $installer->getFkName('googleshopping_types', 'attribute_set_id', 'eav_attribute_set', 'attribute_set_id'),
        'attribute_set_id',
        $this->getTable('eav_attribute_set'),
        'attribute_set_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addIndex(
        $installer->getIdxName(
            'googleshopping_types',
            ['attribute_set_id', 'target_country'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        ['attribute_set_id', 'target_country'],
        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
    )
    ->setComment('Google Content Item Types link Attribute Sets');
$installer->getConnection()->createTable($table);

$table = $connection->newTable($this->getTable('googleshopping_items'))
    ->addColumn(
        'item_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true],
        'Item Id'
    )
    ->addColumn(
        'type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'unsigned' => true, 'default' => 0],
        'Type Id'
    )
    ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'unsigned' => true],
        'Product Id'
    )
    ->addColumn(
        'gcontent_item_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        ['nullable' => false],
        'Google Content Item Id'
    )
    ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['nullable' => false, 'unsigned' => true],
        'Store Id'
    )
    ->addColumn(
        'published',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
        null,
        [],
        'Published date'
    )
    ->addColumn(
        'expires',
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
        null,
        [],
        'Expires date'
    )
    ->addForeignKey(
        $installer->getFkName('googleshopping_items', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id',
        $this->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('googleshopping_items', 'store_id', 'store', 'store_id'),
        'store_id',
        $this->getTable('store'),
        'store_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addIndex(
        $installer->getIdxName('googleshopping_items', ['product_id', 'store_id']),
        ['product_id', 'store_id']
    )
    ->setComment('Google Content Items Products');
$installer->getConnection()->createTable($table);

$table = $connection->newTable($this->getTable('googleshopping_attributes'))
    ->addColumn(
        'id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        10,
        ['identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true],
        'Id'
    )
    ->addColumn(
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        null,
        ['nullable' => false, 'unsigned' => true],
        'Attribute Id'
    )
    ->addColumn(
        'gcontent_attribute',
        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        255,
        ['nullable' => false],
        'Google Content Attribute'
    )
    ->addColumn(
        'type_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        ['nullable' => false, 'unsigned' => true],
        'Type Id'
    )
    ->addForeignKey(
        $installer->getFkName('googleshopping_attributes', 'attribute_id', 'eav_attribute', 'attribute_id'),
        'attribute_id',
        $this->getTable('eav_attribute'),
        'attribute_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('googleshopping_attributes', 'type_id', 'googleshopping_types', 'type_id'),
        'type_id',
        $this->getTable('googleshopping_types'),
        'type_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Google Content Attributes link Product Attributes');
$installer->getConnection()->createTable($table);

$installer->endSetup();
