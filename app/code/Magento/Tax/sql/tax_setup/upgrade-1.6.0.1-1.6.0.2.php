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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer \Magento\Tax\Model\Resource\Setup */
$installer = $this;

/**
 * Create table 'sales_order_tax_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_order_tax_item'))
    ->addColumn('tax_item_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tax Item Id')
    ->addColumn('tax_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Tax Id')
    ->addColumn('item_id', \Magento\DB\Ddl\Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Item Id')
    ->addIndex($installer->getIdxName('sales_order_tax_item', array('tax_id')),
        array('tax_id'))
    ->addIndex($installer->getIdxName('sales_order_tax_item', array('item_id')),
        array('item_id'))
    ->addIndex(
        $installer->getIdxName(
            'sales_order_tax_item', array('tax_id', 'item_id'),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        ),
        array('tax_id', 'item_id'), array('type' => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName(
            'sales_order_tax_item',
            'tax_id',
            'sales_order_tax',
            'tax_id'
        ),
        'tax_id',
        $installer->getTable('sales_order_tax'),
        'tax_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\DB\Ddl\Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'sales_order_tax_item',
            'item_id',
            'sales_flat_order_item',
            'item_id'
        ),
        'item_id',
        $installer->getTable('sales_flat_order_item'),
        'item_id',
        \Magento\DB\Ddl\Table::ACTION_CASCADE,
        \Magento\DB\Ddl\Table::ACTION_CASCADE
    )
    ->setComment('Sales Order Tax Item');
$installer->getConnection()->createTable($table);
