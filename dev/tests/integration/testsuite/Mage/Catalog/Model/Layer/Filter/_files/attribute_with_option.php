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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* Create attribute */
$installer = new Mage_Catalog_Model_Resource_Setup('catalog_setup');
$attribute = new Mage_Catalog_Model_Resource_Eav_Attribute();
$attribute->setData(
    array(
        'attribute_code'    => 'attribute_with_option',
        'entity_type_id'    => $installer->getEntityTypeId('catalog_product'),
        'is_global'         => 1,
        'frontend_input'    => 'select',
        'is_filterable'     => 1,
        'option' => array(
            'value' => array(
                'option_0' => array(0 => 'Option Label'),
            )
        ),
        'backend_type' => 'int',
    )
);
$attribute->save();

/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());

/* Create simple products per each option */
$options = new Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection();
$options->setAttributeFilter($attribute->getId());

foreach ($options as $option) {
    $product = new Mage_Catalog_Model_Product();
    $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
        ->setWebsiteIds(array(1))
        ->setName('Simple Product ' . $option->getId())
        ->setSku('simple_product_' . $option->getId())
        ->setPrice(10)
        ->setCategoryIds(array(2))
        ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
        ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
        ->setStockData(
            array(
                'use_config_manage_stock'   => 1,
                'qty'                       => 5,
                'is_in_stock'               => 1,
            )
        )
        ->save();

    Mage::getSingleton('Mage_Catalog_Model_Product_Action')->updateAttributes(
        array($product->getId()),
        array($attribute->getAttributeCode() => $option->getId()),
        $product->getStoreId()
    );
}
