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
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Create attribute */
/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('Mage_Catalog_Model_Resource_Setup', array('resourceName' => 'catalog_setup'));
/** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
$attribute = Mage::getResourceModel('Mage_Catalog_Model_Resource_Eav_Attribute');
$attribute->setData(array(
    'attribute_code'                => 'test_configurable',
    'entity_type_id'                => $installer->getEntityTypeId('catalog_product'),
    'is_global'                     => 1,
    'is_user_defined'               => 1,
    'frontend_input'                => 'select',
    'is_unique'                     => 0,
    'is_required'                   => 1,
    'is_configurable'               => 1,
    'is_searchable'                 => 0,
    'is_visible_in_advanced_search' => 0,
    'is_comparable'                 => 0,
    'is_filterable'                 => 0,
    'is_filterable_in_search'       => 0,
    'is_used_for_promo_rules'       => 0,
    'is_html_allowed_on_front'      => 1,
    'is_visible_on_front'           => 0,
    'used_in_product_listing'       => 0,
    'used_for_sort_by'              => 0,
    'frontend_label'                => array('Test Configurable'),
    'backend_type'                  => 'int',
    'option'                        => array(
        'value' => array(
            'option_0' => array('Option 1'),
            'option_1' => array('Option 2'),
        ),
        'order' => array(
            'option_0' => 1,
            'option_1' => 2,
        )
    ),
));
$attribute->save();

/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());

/* Create simple products per each option */
/** @var $options Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection */
$options = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection');
$options->setAttributeFilter($attribute->getId());

$attributeValues = array();
$productIds = array();
foreach ($options as $option) {
    /** @var $product Mage_Catalog_Model_Product */
    $product = Mage::getModel('Mage_Catalog_Model_Product');
    $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        ->setId($option->getId() * 10)
        ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
        ->setWebsiteIds(array(1))
        ->setName('Configurable Option' . $option->getId())
        ->setSku('simple_' . $option->getId())
        ->setPrice(10)
        ->setTestConfigurable($option->getId())
        ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
        ->setStockData(array(
            'use_config_manage_stock' => 1,
            'qty'                     => 100,
            'is_qty_decimal'          => 0,
            'is_in_stock'             => 1,
        ))
        ->save();
    $attributeValues[] = array(
        'label'         => 'test',
        'attribute_id'  => $attribute->getId(),
        'value_index'   => $option->getId(),
        'is_percent'    => false,
        'pricing_value' => 5,
    );
    $productIds[] = $product->getId();
}

/** @var $product Mage_Catalog_Model_Product */
$product = Mage::getModel('Mage_Catalog_Model_Product');
$product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
    ->setId(1)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds(array(1))
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setPrice(100)
    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    ->setStockData(array(
        'use_config_manage_stock' => 1,
        'is_in_stock'             => 1,
    ))
    ->setAssociatedProductIds($productIds)
    ->setConfigurableAttributesData(array(array(
        'attribute_id'   => $attribute->getId(),
        'attribute_code' => $attribute->getAttributeCode(),
        'frontend_label' => 'test',
        'values'         => $attributeValues,
    )))
    ->save();
