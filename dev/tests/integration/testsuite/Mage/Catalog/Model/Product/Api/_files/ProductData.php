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


return array(
    'create' => array(
        'type' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'set' => 4,
        'sku' => 'simple' . uniqid(),
        'productData' => (object)array(
            'name' => 'test',
            'description' => 'description',
            'short_description' => 'short description',
            'weight' => 1,
            'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
            'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            'price' => 9.99,
            'tax_class_id' => 2,
            'stock_data' => array(
                'manage_stock' => 1,
                'qty' => 10,
                'backorders' => 1,
                'is_in_stock' => '1',
            )
        )
    ),
    'update' => array(
        'productData' => (object)array(
            'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED, //required to see product on backend
            'name' => 'Simple Product Updated', //test update method
        )
    ),
    'update_custom_store' => array(
        'productData' => (object)array(
            'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED, //required to see product on backend
            'name' => 'Simple Product Updated Custom Store', //test update method
        ),
        'store' => 'test_store'
    ),
    'update_default_store' => array(
        'productData' => (object)array(
            'description' => 'Updated description'
        )
    ),
    'create_with_attributes_soapv2' => array(
        'type' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'set' => 4,
        'sku' => 'simple' . uniqid(),
        'productData' => (object)array(
            'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED, //required to see product on backend
            'name' => 'Product with attributes',
            'description' => 'description',
            'short_description' => 'short description',
            'weight' => 1,
            'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            'price' => 9.99,
            'tax_class_id' => 2,
            'additional_attributes' => (object)array(
                'singleData' => array(
                    (object)array(
                        'key' => 'a_text_api',
                        'value' => 'qqq123'
                    ),
                    (object)array(
                        'key' => 'a_select_api',
                        'value' => '__PLACEHOLDER__'
                    ),
                    (object)array(
                        'key' => 'a_text_ins',
                        'value' => 'qqq123'
                    ),
                    (object)array(
                        'key' => 'a_select_ins',
                        'value' => '__PLACEHOLDER__'
                    ),
                ),
                'multi_data' => array()
            )
        )
    ),
    'create_full_fledged' => array(
        'sku' => 'simple' . uniqid(),
        'attribute_set_id' => 4,
        'type_id' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'name' => 'Simple Product',
        'website_ids' => array(Mage::app()->getStore()->getWebsiteId()),
        'description' => '...',
        'short_description' => '...',
        'price' => 0.99,
        'tax_class_id' => 2,
        'weight' => 1,
        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
        'special_from_date' => false, // to avoid set this attr to '' which leads to unpredictable bugs
        'stock_data' => array(
            'manage_stock' => 1,
            'qty' => 10,
            'backorders' => 1,
            'is_in_stock' => '1',
        )
    ),
    'create_full' => array(
        'soap' => array(
            'type' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            'set' => 4,
            'sku' => 'simple' . uniqid(),
            'productData' => (object)array(
                'name' => 'Simple Product',
                'website_ids' => array(Mage::app()->getStore()->getWebsiteId()),
                'description' => '...',
                'short_description' => '...',
                'price' => 0.99,
                'tax_class_id' => 2,
                'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                'status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                'weight' => 1,
                'stock_data' => array(
                    'manage_stock' => 1,
                    'qty' => 10,
                    'backorders' => 1,
                    'is_in_stock' => '1',
                )
            )
        )
    )
);
