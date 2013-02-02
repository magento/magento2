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

/** @var Mage_Catalog_Model_Product_Attribute_Set_Api $attrSetApi */
$attrSetApi = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Set_Api');
Mage::register(
    'testAttributeSetId',
    $attrSetApi->create('Test Attribute Set Fixture ' . mt_rand(1000, 9999), 4)
);

$attributeSetFixture = simplexml_load_file(__DIR__ . '/_data/xml/AttributeSet.xml');
$data = Magento_Test_Helper_Api::simpleXmlToArray($attributeSetFixture->attributeEntityToCreate);
$data['attribute_code'] = $data['attribute_code'] . '_' . mt_rand(1000, 9999);

$testAttributeSetAttrIdsArray = array();

$attrApi = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Api');
$testAttributeSetAttrIdsArray[0] = $attrApi->create($data);
Mage::register('testAttributeSetAttrIdsArray', $testAttributeSetAttrIdsArray);
