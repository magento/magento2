<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Setup\Module\SetupModule */
$this->startSetup();
/**
 * Create table 'weee_tax'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('weee_tax')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Value Id'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Website Id'
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Entity Id'
)->addColumn(
    'country',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    2,
    ['nullable' => true],
    'Country'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Value'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false, 'default' => '*'],
    'State'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Attribute Id'
)->addColumn(
    'entity_type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Entity Type Id'
)->addIndex(
    $this->getIdxName('weee_tax', ['website_id']),
    ['website_id']
)->addIndex(
    $this->getIdxName('weee_tax', ['entity_id']),
    ['entity_id']
)->addIndex(
    $this->getIdxName('weee_tax', ['country']),
    ['country']
)->addIndex(
    $this->getIdxName('weee_tax', ['attribute_id']),
    ['attribute_id']
)->addForeignKey(
    $this->getFkName('weee_tax', 'country', 'directory_country', 'country_id'),
    'country',
    $this->getTable('directory_country'),
    'country_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('weee_tax', 'entity_id', 'catalog_product_entity', 'entity_id'),
    'entity_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('weee_tax', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $this->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('weee_tax', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $this->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Weee Tax'
);
$this->getConnection()->createTable($table);

$this->endSetup();
