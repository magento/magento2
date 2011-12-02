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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Create order account form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Form_Account extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
{
    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-account';
    }

    /**
     * Return header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('Mage_Sales_Helper_Data')->__('Account Information');
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Form_Account
     */
    protected function _prepareForm()
    {
        /* @var $customerModel Mage_Customer_Model_Customer */
        $customerModel = Mage::getModel('Mage_Customer_Model_Customer');

        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm   = Mage::getModel('Mage_Customer_Model_Form');
        $customerForm->setFormCode('adminhtml_checkout')
            ->setStore($this->getStore())
            ->setEntity($customerModel);

        // prepare customer attributes to show
        $attributes     = array();

        // add system required attributes
        foreach ($customerForm->getSystemAttributes() as $attribute) {
            /* @var $attribute Mage_Customer_Model_Attribute */
            if ($attribute->getIsRequired()) {
                $attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        if ($this->getQuote()->getCustomerIsGuest()) {
            unset($attributes['group_id']);
        }

        // add user defined attributes
        foreach ($customerForm->getUserAttributes() as $attribute) {
            /* @var $attribute Mage_Customer_Model_Attribute */
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        $fieldset = $this->_form->addFieldset('main', array());

        $this->_addAttributesToForm($attributes, $fieldset);

        $this->_form->addFieldNameSuffix('order[account]');
        $this->_form->setValues($this->getFormValues());

        return $this;
    }

    /**
     * Add additional data to form element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Form_Abstract
     */
    protected function _addAdditionalFormElementData(Varien_Data_Form_Element_Abstract $element)
    {
        switch ($element->getId()) {
            case 'email':
                $element->setRequired(0);
                $element->setClass('validate-email');
                break;
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
        $data = $this->getCustomer()->getData();
        foreach ($this->getQuote()->getData() as $key => $value) {
            if (strpos($key, 'customer_') === 0) {
                $data[substr($key, 9)] = $value;
            }
        }

        if ($this->getQuote()->getCustomerEmail()) {
            $data['email']  = $this->getQuote()->getCustomerEmail();
        }

        return $data;
    }
}
