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

$data = require dirname(__FILE__) . '/ProductAttributeData.php';
// add product attributes via installer
$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
$installer->addAttribute(
    'catalog_product',
    $data['create_text_installer']['code'],
    $data['create_text_installer']['attributeData']
);
$installer->addAttribute(
    'catalog_product',
    $data['create_select_installer']['code'],
    $data['create_select_installer']['attributeData']
);

//add attributes to default attribute set via installer
$installer->addAttributeToSet('catalog_product', 4, 'Default', $data['create_text_installer']['code']);
$installer->addAttributeToSet('catalog_product', 4, 'Default', $data['create_select_installer']['code']);

$attribute = Mage::getModel('Mage_Eav_Model_Entity_Attribute');
$attribute->loadByCode('catalog_product', $data['create_select_installer']['code']);
$collection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection')
    ->setAttributeFilter($attribute->getId())
    ->load();
$options = $collection->toOptionArray();
$optionValueInstaller = $options[1]['value'];

//add product attributes via api model
$model = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Api');
$response1 = $model->create($data['create_text_api']);
$response2 = $model->create($data['create_select_api']);

//add options
$model = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Api');
$model->addOption($response2, $data['create_select_api_options'][0]);
$model->addOption($response2, $data['create_select_api_options'][1]);
$options = $model->options($response2);
$optionValueApi = $options[1]['value'];

//add attributes to default attribute set via api model
$model = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Set_Api');
$model->attributeAdd($response1, 4);
$model->attributeAdd($response2, 4);

$attributes = array($response1, $response2);
Mage::register('attributes', $attributes);
Mage::register('optionValueApi', $optionValueApi);
Mage::register('optionValueInstaller', $optionValueInstaller);
