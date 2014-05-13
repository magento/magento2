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

/** @var $installer \Magento\Sales\Model\Resource\Setup */
$installer = $this;

$subSelect = $installer->getConnection()->select()->from(
    array('citem' => $installer->getTable('sales_flat_creditmemo_item')),
    array(
        'amount_refunded' => 'SUM(citem.row_total)',
        'base_amount_refunded' => 'SUM(citem.base_row_total)',
        'base_tax_refunded' => 'SUM(citem.base_tax_amount)',
        'discount_refunded' => 'SUM(citem.discount_amount)',
        'base_discount_refunded' => 'SUM(citem.base_discount_amount)'
    )
)->joinLeft(
    array('c' => $installer->getTable('sales_flat_creditmemo')),
    'c.entity_id = citem.parent_id',
    array()
)->joinLeft(
    array('o' => $installer->getTable('sales_flat_order')),
    'o.entity_id = c.order_id',
    array()
)->joinLeft(
    array('oitem' => $installer->getTable('sales_flat_order_item')),
    'oitem.order_id = o.entity_id AND oitem.product_id=citem.product_id',
    array('item_id')
)->group(
    'oitem.item_id'
);

$select = $installer->getConnection()->select()->from(
    array('selected' => $subSelect),
    array(
        'amount_refunded' => 'amount_refunded',
        'base_amount_refunded' => 'base_amount_refunded',
        'base_tax_refunded' => 'base_tax_refunded',
        'discount_refunded' => 'discount_refunded',
        'base_discount_refunded' => 'base_discount_refunded'
    )
)->where(
    'main.item_id = selected.item_id'
);

$updateQuery = $installer->getConnection()->updateFromSelect(
    $select,
    array('main' => $installer->getTable('sales_flat_order_item'))
);

$installer->getConnection()->query($updateQuery);
