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

/**
 * Install grouped product link type
 */
$data = array(
    'link_type_id' => \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED,
    'code' => 'super'
);
$installer->getConnection()->insertOnDuplicate($installer->getTable('catalog_product_link_type'), $data);

/**
 * Install grouped product link attributes
 */
$select = $installer->getConnection()->select()->from(
    array('c' => $installer->getTable('catalog_product_link_attribute'))
)->where(
    "c.link_type_id=?",
    \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED
);
$result = $installer->getConnection()->fetchAll($select);

if (!$result) {

    $data = array(
        array(
            'link_type_id' => \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED,
            'product_link_attribute_code' => 'position',
            'data_type' => 'int'
        ),
        array(
            'link_type_id' => \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED,
            'product_link_attribute_code' => 'qty',
            'data_type' => 'decimal'
        )
    );

    $installer->getConnection()->insertMultiple($installer->getTable('catalog_product_link_attribute'), $data);
}

$field = 'country_of_manufacture';
$applyTo = explode(',', $installer->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to'));
if (!in_array('grouped', $applyTo)) {
    $applyTo[] = 'grouped';
    $installer->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to', implode(',', $applyTo));
}
