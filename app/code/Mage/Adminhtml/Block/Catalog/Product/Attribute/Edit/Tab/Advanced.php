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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product attribute add/edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Advanced extends Mage_Backend_Block_Widget_Form
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Advanced
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            array(
                'legend' => $this->__('Advanced Attribute Properties'),
                'collapsable' => true
            )
        );

        $yesno = Mage::getModel('Mage_Backend_Model_Config_Source_Yesno')->toOptionArray();

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            array(
                'name' => 'attribute_code',
                'label' => $this->__('Attribute Code'),
                'title' => $this->__('Attribute Code'),
                'note' => $this->__(
                    'For internal use. Must be unique with no spaces. Maximum length of attribute code must be less than %s symbols',
                    Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
            )
        );

        $fieldset->addField(
            'default_value_text',
            'text',
            array(
                'name' => 'default_value_text',
                'label' => $this->__('Default Value'),
                'title' => $this->__('Default Value'),
                'value' => $attributeObject->getDefaultValue(),
            )
        );

        $fieldset->addField(
            'default_value_yesno',
            'select',
            array(
                'name' => 'default_value_yesno',
                'label' => $this->__('Default Value'),
                'title' => $this->__('Default Value'),
                'values' => $yesno,
                'value' => $attributeObject->getDefaultValue(),
            )
        );

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'default_value_date',
            'date',
            array(
                'name' => 'default_value_date',
                'label' => $this->__('Default Value'),
                'title' => $this->__('Default Value'),
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                'value' => $attributeObject->getDefaultValue(),
                'date_format' => $dateFormat
            )
        );

        $fieldset->addField(
            'default_value_textarea',
            'textarea',
            array(
                'name' => 'default_value_textarea',
                'label' => $this->__('Default Value'),
                'title' => $this->__('Default Value'),
                'value' => $attributeObject->getDefaultValue(),
            )
        );

        $fieldset->addField(
            'is_unique',
            'select',
            array(
                'name' => 'is_unique',
                'label' => $this->__('Unique Value'),
                'title' => $this->__('Unique Value (not shared with other products)'),
                'note' => $this->__('Not shared with other products'),
                'values' => $yesno,
            )
        );

        $fieldset->addField(
            'frontend_class',
            'select',
            array(
                'name' => 'frontend_class',
                'label' => $this->__('Input Validation for Store Owner'),
                'title' => $this->__('Input Validation for Store Owner'),
                'values' => Mage::helper('Mage_Eav_Helper_Data')->getFrontendClasses(
                    $attributeObject->getEntityType()->getEntityTypeCode()
                )
            )
        );

        if ($attributeObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            if (!$attributeObject->getIsUserDefined()) {
                $form->getElement('is_unique')->setDisabled(1);
            }
        }

        $yesnoSource = Mage::getModel('Mage_Backend_Model_Config_Source_Yesno')->toOptionArray();

        $scopes = array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE =>Mage::helper('Mage_Catalog_Helper_Data')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE =>Mage::helper('Mage_Catalog_Helper_Data')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL =>Mage::helper('Mage_Catalog_Helper_Data')->__('Global'),
        );

        if ($attributeObject->getAttributeCode() == 'status' || $attributeObject->getAttributeCode() == 'tax_class_id') {
            unset($scopes[Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE]);
        }

        $fieldset->addField('is_global', 'select', array(
            'name'  => 'is_global',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Scope'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Scope'),
            'note'  => Mage::helper('Mage_Catalog_Helper_Data')->__('Declare attribute value saving scope'),
            'values'=> $scopes
        ), 'attribute_code');


        $fieldset->addField('is_configurable', 'select', array(
            'name' => 'is_configurable',
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Use To Create Configurable Product'),
            'values' => $yesnoSource,
        ));
        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize form fileds values
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Advanced
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    private function getAttributeObject()
    {
        return Mage::registry('entity_attribute');
    }
}
