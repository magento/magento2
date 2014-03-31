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
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Core\Model\Resource\Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'importexport_importdata'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('importexport_importdata')
)->addColumn(
    'id',
    \Magento\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Id'
)->addColumn(
    'entity',
    \Magento\DB\Ddl\Table::TYPE_TEXT,
    50,
    array('nullable' => false),
    'Entity'
)->addColumn(
    'behavior',
    \Magento\DB\Ddl\Table::TYPE_TEXT,
    10,
    array('nullable' => false, 'default' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND),
    'Behavior'
)->addColumn(
    'data',
    \Magento\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('default' => ''),
    'Data'
)->setComment(
    'Import Data Table'
);
$installer->getConnection()->createTable($table);

/**
 * Add unique key for 'catalog_product_link_attribute_int' table
 */
$installer->getConnection()->addIndex(
    $installer->getTable('catalog_product_link_attribute_int'),
    $installer->getIdxName(
        'catalog_product_link_attribute_int',
        array('product_link_attribute_id', 'link_id'),
        \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('product_link_attribute_id', 'link_id'),
    \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
);

/**
 * Add foreign keys for 'catalog_product_link_attribute_int' table
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName('catalog_product_link_attribute_int', 'link_id', 'catalog_product_link', 'link_id'),
    $installer->getTable('catalog_product_link_attribute_int'),
    'link_id',
    $installer->getTable('catalog_product_link'),
    'link_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'catalog_product_link_attribute_int',
        'product_link_attribute_id',
        'catalog_product_link_attribute',
        'product_link_attribute_id'
    ),
    $installer->getTable('catalog_product_link_attribute_int'),
    'product_link_attribute_id',
    $installer->getTable('catalog_product_link_attribute'),
    'product_link_attribute_id'
);

$installer->endSetup();
