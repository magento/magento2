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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product attribute add/edit form main tab
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_attribute = null;

    public function setAttributeObject($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttributeObject()
    {
        if (null === $this->_attribute) {
            return Mage::registry('entity_attribute');
        }
        return $this->_attribute;
    }

    /**
     * Preparing default form elements for editing attribute
     *
     * @return Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => $this->__('Attribute Properties'))
        );

        if ($attributeObject->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', array(
                'name' => 'attribute_id',
            ));
        }

        $this->_addElementTypes($fieldset);

        $yesno = Mage::getModel('Mage_Backend_Model_Config_Source_Yesno')->toOptionArray();

        $labels = $attributeObject->getFrontendLabel();
        $fieldset->addField(
            'attribute_label',
            'text',
            array(
                'name' => 'frontend_label[0]',
                'label' => $this->__('Attribute Label'),
                'title' => $this->__('Attribute Label'),
                'required' => true,
                'value' => is_array($labels) ? $labels[0] : $labels
            )
        );


        $validateClass = sprintf('validate-code validate-length maximum-length-%d',
            Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH);
        $fieldset->addField('attribute_code', 'text', array(
            'name'  => 'attribute_code',
            'label' => $this->__('Attribute Code'),
            'title' => $this->__('Attribute Code'),
            'note'  => $this->__('For internal use. Must be unique with no spaces. Maximum length of attribute code must be less than %s symbols', Mage_Eav_Model_Entity_Attribute::ATTRIBUTE_CODE_MAX_LENGTH),
            'class' => $validateClass,
            'required' => true,
        ));

        $inputTypes = Mage::getModel('Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype')->toOptionArray();

        $fieldset->addField('frontend_input', 'select', array(
            'name' => 'frontend_input',
            'label' => $this->__('Catalog Input Type for Store Owner'),
            'title' => $this->__('Catalog Input Type for Store Owner'),
            'value' => 'text',
            'values'=> $inputTypes
        ));

        $fieldset->addField(
            'is_required',
            'select',
            array(
                'name' => 'is_required',
                'label' => $this->__('Values Required'),
                'title' => $this->__('Values Required'),
                'values' => $yesno,
            )
        );

        $fieldset->addField('default_value_text', 'text', array(
            'name' => 'default_value_text',
            'label' => $this->__('Default Value'),
            'title' => $this->__('Default Value'),
            'value' => $attributeObject->getDefaultValue(),
        ));

        $fieldset->addField('default_value_yesno', 'select', array(
            'name' => 'default_value_yesno',
            'label' => $this->__('Default Value'),
            'title' => $this->__('Default Value'),
            'values' => $yesno,
            'value' => $attributeObject->getDefaultValue(),
        ));

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField('default_value_date', 'date', array(
            'name'   => 'default_value_date',
            'label'  => $this->__('Default Value'),
            'title'  => $this->__('Default Value'),
            'image'  => $this->getViewFileUrl('images/grid-cal.gif'),
            'value'  => $attributeObject->getDefaultValue(),
            'date_format' => $dateFormat
        ));

        $fieldset->addField('default_value_textarea', 'textarea', array(
            'name' => 'default_value_textarea',
            'label' => $this->__('Default Value'),
            'title' => $this->__('Default Value'),
            'value' => $attributeObject->getDefaultValue(),
        ));

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

        $fieldset->addField('frontend_class', 'select', array(
            'name'  => 'frontend_class',
            'label' => $this->__('Input Validation for Store Owner'),
            'title' => $this->__('Input Validation for Store Owner'),
            'values'=> Mage::helper('Mage_Eav_Helper_Data')->getFrontendClasses(
                $attributeObject->getEntityType()->getEntityTypeCode()
            )
        ));

        if ($attributeObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            $form->getElement('frontend_input')->setDisabled(1);
            if (!$attributeObject->getIsUserDefined()) {
                $form->getElement('is_unique')->setDisabled(1);
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     *
     * @return Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
     */
    protected function _initFormValues()
    {
        Mage::dispatchEvent('adminhtml_block_eav_attribute_edit_form_init', array('form' => $this->getForm()));
        $this->getForm()
            ->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * This method is called before rendering HTML
     *
     * @return Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $attributeObject = $this->getAttributeObject();
        if ($attributeObject->getId()) {
            $form = $this->getForm();
            $disableAttributeFields = Mage::helper('Mage_Eav_Helper_Data')
                ->getAttributeLockedFields($attributeObject->getEntityType()->getEntityTypeCode());
            if (isset($disableAttributeFields[$attributeObject->getAttributeCode()])) {
                foreach ($disableAttributeFields[$attributeObject->getAttributeCode()] as $field) {
                    if ($elm = $form->getElement($field)) {
                        $elm->setDisabled(1);
                        $elm->setReadonly(1);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Processing block html after rendering
     * Adding js block to the end of this block
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()
            ->createBlock('Mage_Eav_Block_Adminhtml_Attribute_Edit_Js')->toHtml();
        return $html.$jsScripts;
    }

}
