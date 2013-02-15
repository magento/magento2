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
 * Sales Order Create Form Abstract Block
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
    extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    /**
     * Data Form object
     *
     * @var Varien_Data_Form
     */
    protected $_form;

    /**
     * Prepare global layout
     * Add renderers to Varien_Data_Form
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock(
                'Mage_Adminhtml_Block_Widget_Form_Renderer_Element',
                $this->getNameInLayout() . '_element'
            )
        );
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(
                'Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset',
                $this->getNameInLayout() . '_fieldset'
            )
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return $this;
    }

    /**
     * Return Form object
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        if (is_null($this->_form)) {
            $this->_form = new Varien_Data_Form();
            $this->_prepareForm();
        }

        return $this->_form;
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
     */
    abstract protected function _prepareForm();

    /**
     * Return array of additional form element types by type
     *
     * @return array
     */
    protected function _getAdditionalFormElementTypes()
    {
        return array(
            'file'      => 'Mage_Adminhtml_Block_Customer_Form_Element_File',
            'image'     => 'Mage_Adminhtml_Block_Customer_Form_Element_Image',
            'boolean'   => 'Mage_Adminhtml_Block_Customer_Form_Element_Boolean',
        );
    }

    /**
     * Return array of additional form element renderers by element id
     *
     * @return array
     */
    protected function _getAdditionalFormElementRenderers()
    {
        return array(
            'region'    => $this->getLayout()->createBlock('Mage_Adminhtml_Block_Customer_Edit_Renderer_Region'),
        );
    }

    /**
     * Add additional data to form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
     */
    protected function _addAdditionalFormElementData(Varien_Data_Form_Element_Abstract $element)
    {
        return $this;
    }

    /**
     * Add rendering EAV attributes to Form element
     *
     * @param array|Varien_Data_Collection $attributes
     * @param Varien_Data_Form_Abstract $form
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
     */
    protected function _addAttributesToForm($attributes, Varien_Data_Form_Abstract $form)
    {
        // add additional form types
        $types = $this->_getAdditionalFormElementTypes();
        foreach ($types as $type => $className) {
            $form->addType($type, $className);
        }
        $renderers = $this->_getAdditionalFormElementRenderers();

        foreach ($attributes as $attribute) {
            /** @var $attribute Mage_Customer_Model_Attribute */
            $attribute->setStoreId(Mage::getSingleton('Mage_Adminhtml_Model_Session_Quote')->getStoreId());
            $inputType = $attribute->getFrontend()->getInputType();

            if ($inputType) {
                $element = $form->addField($attribute->getAttributeCode(), $inputType, array(
                    'name'      => $attribute->getAttributeCode(),
                    'label'     => $this->__($attribute->getStoreLabel()),
                    'class'     => $attribute->getFrontend()->getClass(),
                    'required'  => $attribute->getIsRequired(),
                ));
                if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
                $element->setEntityAttribute($attribute);
                $this->_addAdditionalFormElementData($element);

                if (!empty($renderers[$attribute->getAttributeCode()])) {
                    $element->setRenderer($renderers[$attribute->getAttributeCode()]);
                }

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($attribute->getFrontend()->getSelectOptions());
                } else if ($inputType == 'date') {
                    $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                    $element->setImage($this->getViewFileUrl('images/grid-cal.gif'));
                    $element->setDateFormat($format);
                }
            }
        }

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return array();
    }
}
