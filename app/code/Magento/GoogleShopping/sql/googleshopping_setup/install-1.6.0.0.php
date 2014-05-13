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

/**
 * GoogleShopping install
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
/** @var $installer \Magento\Framework\Module\Setup */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();

$table = $connection->newTable(
    $this->getTable('googleshopping_types')
)->addColumn(
    'type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Type ID'
)->addColumn(
    'attribute_set_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Attribute Set Id'
)->addColumn(
    'target_country',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    2,
    array('nullable' => false, 'default' => 'US'),
    'Target country'
)->addForeignKey(
    $installer->getFkName('googleshopping_types', 'attribute_set_id', 'eav_attribute_set', 'attribute_set_id'),
    'attribute_set_id',
    $this->getTable('eav_attribute_set'),
    'attribute_set_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addIndex(
    $installer->getIdxName(
        'googleshopping_types',
        array('attribute_set_id', 'target_country'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('attribute_set_id', 'target_country'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->setComment(
    'Google Content Item Types link Attribute Sets'
);
$installer->getConnection()->createTable($table);

$table = $connection->newTable(
    $this->getTable('googleshopping_items')
)->addColumn(
    'item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true),
    'Item Id'
)->addColumn(
    'type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true, 'default' => 0),
    'Type Id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Product Id'
)->addColumn(
    'gcontent_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Google Content Item Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Store Id'
)->addColumn(
    'published',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
    null,
    array(),
    'Published date'
)->addColumn(
    'expires',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
    null,
    array(),
    'Expires date'
)->addForeignKey(
    $installer->getFkName('googleshopping_items', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('googleshopping_items', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addIndex(
    $installer->getIdxName('googleshopping_items', array('product_id', 'store_id')),
    array('product_id', 'store_id')
)->setComment(
    'Google Content Items Products'
);
$installer->getConnection()->createTable($table);

$table = $connection->newTable(
    $this->getTable('googleshopping_attributes')
)->addColumn(
    'id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    10,
    array('identity' => true, 'nullable' => false, 'unsigned' => true, 'primary' => true),
    'Id'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Attribute Id'
)->addColumn(
    'gcontent_attribute',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Google Content Attribute'
)->addColumn(
    'type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'unsigned' => true),
    'Type Id'
)->addForeignKey(
    $installer->getFkName('googleshopping_attributes', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $this->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('googleshopping_attributes', 'type_id', 'googleshopping_types', 'type_id'),
    'type_id',
    $this->getTable('googleshopping_types'),
    'type_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Google Content Attributes link Product Attributes'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
