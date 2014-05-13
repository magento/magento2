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

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;
$groupPriceAttrId = $installer->getAttribute('catalog_product', 'group_price', 'attribute_id');
$priceAttrId = $installer->getAttribute('catalog_product', 'price', 'attribute_id');
$connection = $installer->getConnection();

// update sort_order of Group Price attribute to be after Price
$select = $connection->select()->join(
    array('t2' => $installer->getTable('eav_entity_attribute')),
    't1.attribute_group_id = t2.attribute_group_id',
    array('sort_order' => new \Zend_Db_Expr('t2.sort_order + 1'))
)->where(
    't1.attribute_id = ?',
    $groupPriceAttrId
)->where(
    't2.attribute_id = ?',
    $priceAttrId
);
$query = $select->crossUpdateFromSelect(array('t1' => $installer->getTable('eav_entity_attribute')));
$connection->query($query);

// update sort_order of all other attributes to be after Group Price
$select = $connection->select()->join(
    array('t2' => $installer->getTable('eav_entity_attribute')),
    't1.attribute_group_id = t2.attribute_group_id',
    array('sort_order' => new \Zend_Db_Expr('t1.sort_order + 1'))
)->where(
    't1.attribute_id != ?',
    $groupPriceAttrId
)->where(
    't1.sort_order >= t2.sort_order'
)->where(
    't2.attribute_id = ?',
    $groupPriceAttrId
);
$query = $select->crossUpdateFromSelect(array('t1' => $installer->getTable('eav_entity_attribute')));
$connection->query($query);
