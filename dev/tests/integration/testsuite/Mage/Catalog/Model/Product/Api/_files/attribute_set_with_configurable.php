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

if (!Mage::registry('attribute_set_with_configurable')) {
    define('ATTRIBUTES_COUNT', 2);
    define('ATTRIBUTE_OPTIONS_COUNT', 3);

    /** @var $entityType Mage_Eav_Model_Entity_Type */
    $entityType = Mage::getModel('Mage_Eav_Model_Entity_Type')->loadByCode('catalog_product');

    /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
    $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set');
    $attributeSet->setEntityTypeId($entityType->getEntityTypeId())
        ->setAttributeSetName('Test Attribute Set ' . uniqid());

    $attributeSet->save();
    /** @var $entityType Mage_Eav_Model_Entity_Type */
    $entityType = Mage::getModel('Mage_Eav_Model_Entity_Type')->loadByCode('catalog_product');
    $attributeSet->initFromSkeleton($entityType->getDefaultAttributeSetId())->save();
    Mage::register('attribute_set_with_configurable', $attributeSet);

    /** @var $attributeFixture Mage_Catalog_Model_Resource_Eav_Attribute */
    $attributeFixture = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');

    $attributeFixture->setEntityTypeId(Mage::getModel('Mage_Eav_Model_Entity')->setType('catalog_product')->getTypeId())
        ->setAttributeCode('test_attr_' . uniqid())
        ->setIsUserDefined(true)
        ->setIsVisibleOnFront(false)
        ->setIsRequired(false)
        ->setFrontendLabel(array(0 => 'Test Attr ' . uniqid()))
        ->setApplyTo(array());

    for ($attributeCount = 1; $attributeCount <= ATTRIBUTES_COUNT; $attributeCount++) {
        $attribute = clone $attributeFixture;
        $attribute->setAttributeCode('test_attr_' . uniqid())
            ->setFrontendLabel(array(0 => 'Test Attr ' . uniqid()))
            ->setIsGlobal(true)
            ->setIsConfigurable(true)
            ->setIsRequired(true)
            ->setFrontendInput('select')
            ->setBackendType('int')
            ->setAttributeSetId($attributeSet->getId())
            ->setAttributeGroupId($attributeSet->getDefaultGroupId());

        $options = array();
        for ($optionCount = 0; $optionCount < ATTRIBUTE_OPTIONS_COUNT; $optionCount++) {
            $options['option_' . $optionCount] = array(
                0 => 'Test Option #' . $optionCount
            );
        }
        $attribute->setOption(
            array(
                'value' => $options
            )
        );
        $attribute->save();
        Mage::register('eav_configurable_attribute_' . $attributeCount, $attribute);
        unset($attribute);
    }
}


