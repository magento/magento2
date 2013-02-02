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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $entityType Mage_Eav_Model_Entity_Type */
$entityType = Mage::getModel('Mage_Eav_Model_Entity_Type')->loadByCode('catalog_product');
$taxClasses = Mage::getResourceModel('Mage_Tax_Model_Resource_Class_Collection')->toArray();
$taxClass = reset($taxClasses['items']);

return array(
    'type_id' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    'attribute_set_id' => $entityType->getDefaultAttributeSetId(),
    'sku' => 'simple' . uniqid(),
    'name' => 'Test',
    'description' => 'Test description',
    'short_description' => 'Test short description',
    'weight' => 125,
    'news_from_date' => '02/16/2012',
    'news_to_date' => '16.02.2012',
    'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
    'url_key' => '123!"№;%:?*()_+{}[]\|<>,.?/abc',
    'url_key_create_redirect' => 1,
    'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
    'price' => 25.50,
    'special_price' => 11.2,
    'special_from_date' => '02/16/2012',
    'special_to_date' => '03/17/2012',
    'group_price' => array(
        array('website_id' => 0, 'cust_group' => 1, 'price' => 11)
    ),
    'tier_price' => array(
        array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 5.5, 'price' => 11.054)
    ),
    'msrp_enabled' => 1,
    'msrp_display_actual_price_type' => 1,
    'msrp' => 11.015,
    'enable_googlecheckout' => 1,
    'tax_class_id' => $taxClass['class_id'],
    'meta_title' => 'Test title',
    'meta_keyword' => 'Test keyword',
    'meta_description' => str_pad('', 85, 'a4b'),
    'custom_design' => 'default/default/blank',
    'custom_design_from' => date('Y-m-d'),
    'custom_design_to' => date('Y-m-d', time() + 24 * 3600),
    'custom_layout_update' => '<xml><layout>Test Custom Layout Update</layout></xml>',
    'page_layout' => 'one_column',
    'gift_message_available' => 1,
    'gift_wrapping_available' => 1,
    'gift_wrapping_price' => 0.99,
    'stock_data' => array(
        'manage_stock' => 1,
        'qty' => 1,
        'min_qty' => 1.56,
        'min_sale_qty' => 1,
        'max_sale_qty' => 1,
        'is_qty_decimal' => 0,
        'backorders' => 1,
        'notify_stock_qty' => -50.99,
        'enable_qty_increments' => 0,
        'is_in_stock' => 0
    )
);
