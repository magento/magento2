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

/** @var \Magento\Tax\Model\Resource\Setup $installer */
$installer = $this;
$connection = $installer->getConnection();

/**
 * Add new field to 'sales_order_tax_item'
 */
$connection->addColumn(
    $installer->getTable('sales_order_tax_item'),
    'amount',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        'SCALE' => 4,
        'PRECISION' => 12,
        'NULLABLE' => false,
        'COMMENT' => 'Tax amount for the item and tax rate.'
    ]
);
$connection->addColumn(
    $installer->getTable('sales_order_tax_item'),
    'base_amount',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        'SCALE' => 4,
        'PRECISION' => 12,
        'NULLABLE' => false,
        'COMMENT' => 'Base tax amount for the item and tax rate.'
    ]
);
$connection->addColumn(
    $installer->getTable('sales_order_tax_item'),
    'real_amount',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        'SCALE' => 4,
        'PRECISION' => 12,
        'NULLABLE' => false,
        'COMMENT' => 'Real tax amount for the item and tax rate.'
    ]
);
$connection->addColumn(
    $installer->getTable('sales_order_tax_item'),
    'real_base_amount',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        'SCALE' => 4,
        'PRECISION' => 12,
        'NULLABLE' => false,
        'COMMENT' => 'Real base tax amount for the item and tax rate.'
    ]
);
$connection->addColumn(
    $installer->getTable('sales_order_tax_item'),
    'associated_item_id',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        'UNSIGNED' => true,
        'NULLABLE' => true,
        'COMMENT' => 'Id of the associated item.'
    ]
);
$connection->addColumn(
    $installer->getTable('sales_order_tax_item'),
    'taxable_item_type',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        'length' => 32,
        'NULLABLE' => false,
        'COMMENT' => 'Type of the taxable item.'
    ]
);
$connection->changeColumn(
    $installer->getTable('sales_order_tax_item'),
    'item_id',
    'item_id',
    [
        'TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        'NULLABLE' => true,
        'UNSIGNED' => true,
        'COMMENT' => 'Item Id',
    ]
);
$connection->addForeignKey(
    $installer->getFkName('sales_order_tax_item', 'associated_item_id', 'sales_flat_order_item', 'item_id'),
    $installer->getTable('sales_order_tax_item'),
    'associated_item_id',
    $installer->getTable('sales_flat_order_item'),
    'item_id'
);
