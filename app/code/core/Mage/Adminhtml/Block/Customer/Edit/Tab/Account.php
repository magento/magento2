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
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Account extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('account');

        $customer = Mage::registry('current_customer');

        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('Mage_Customer_Model_Form');
        $customerForm->setEntity($customer)
            ->setFormCode('adminhtml_customer')
            ->initDefaultValues();

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('Mage_Customer_Helper_Data')->__('Account Information'))
        );

        $attributes = $customerForm->getAttributes();
        foreach ($attributes as $attribute) {
            $attribute->unsIsVisible();
        }
        $this->_setFieldset($attributes, $fieldset);

        if ($customer->getId()) {
            $form->getElement('website_id')->setDisabled('disabled');
            $form->getElement('created_in')->setDisabled('disabled');
        } else {
            $fieldset->removeField('created_in');
        }

//        if (Mage::app()->isSingleStoreMode()) {
//            $fieldset->removeField('website_id');
//            $fieldset->addField('website_id', 'hidden', array(
//                'name'      => 'website_id'
//            ));
//            $customer->setWebsiteId(Mage::app()->getStore(true)->getWebsiteId());
//        }

        $customerStoreId = null;
        if ($customer->getId()) {
            $customerStoreId = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
        }

        $prefixElement = $form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->helper('Mage_Customer_Helper_Data')->getNamePrefixOptions($customerStoreId);
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField($prefixElement->getId(),
                    'select',
                    $prefixElement->getData(),
                    $form->getElement('group_id')->getId()
                );
                $prefixField->setValues($prefixOptions);
                if ($customer->getId()) {
                    $prefixField->addElementValues($customer->getPrefix());
                }

            }
        }

        $suffixElement = $form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->helper('Mage_Customer_Helper_Data')->getNameSuffixOptions($customerStoreId);
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField($suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
                if ($customer->getId()) {
                    $suffixField->addElementValues($customer->getSuffix());
                }
            }
        }

        if ($customer->getId()) {
            if (!$customer->isReadonly()) {
                // add password management fieldset
                $newFieldset = $form->addFieldset(
                    'password_fieldset',
                    array('legend'=>Mage::helper('Mage_Customer_Helper_Data')->__('Password Management'))
                );
                // New customer password
                $field = $newFieldset->addField('new_password', 'text',
                    array(
                        'label' => Mage::helper('Mage_Customer_Helper_Data')->__('New Password'),
                        'name'  => 'new_password',
                        'class' => 'validate-new-password'
                    )
                );
                $field->setRenderer(
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Customer_Edit_Renderer_Newpass')
                );

                // prepare customer confirmation control (only for existing customers)
                $confirmationKey = $customer->getConfirmation();
                if ($confirmationKey || $customer->isConfirmationRequired()) {
                    $confirmationAttribute = $customer->getAttribute('confirmation');
                    if (!$confirmationKey) {
                        $confirmationKey = $customer->getRandomConfirmationKey();
                    }
                    $element = $fieldset->addField('confirmation', 'select', array(
                        'name'  => 'confirmation',
                        'label' => Mage::helper('Mage_Customer_Helper_Data')->__($confirmationAttribute->getFrontendLabel()),
                    ))->setEntityAttribute($confirmationAttribute)
                        ->setValues(array('' => 'Confirmed', $confirmationKey => 'Not confirmed'));

                    // prepare send welcome email checkbox, if customer is not confirmed
                    // no need to add it, if website id is empty
                    if ($customer->getConfirmation() && $customer->getWebsiteId()) {
                        $fieldset->addField('sendemail', 'checkbox', array(
                            'name'  => 'sendemail',
                            'label' => Mage::helper('Mage_Customer_Helper_Data')->__('Send Welcome Email after Confirmation')
                        ));
                        $customer->setData('sendemail', '1');
                    }
                }
            }
        } else {
            $newFieldset = $form->addFieldset(
                'password_fieldset',
                array('legend'=>Mage::helper('Mage_Customer_Helper_Data')->__('Password Management'))
            );
            $field = $newFieldset->addField('password', 'text',
                array(
                    'label' => Mage::helper('Mage_Customer_Helper_Data')->__('Password'),
                    'class' => 'input-text required-entry validate-password',
                    'name'  => 'password',
                    'required' => true
                )
            );
            $field->setRenderer($this->getLayout()->createBlock('Mage_Adminhtml_Block_Customer_Edit_Renderer_Newpass'));

            // prepare send welcome email checkbox
            $fieldset->addField('sendemail', 'checkbox', array(
                'label' => Mage::helper('Mage_Customer_Helper_Data')->__('Send Welcome Email'),
                'name'  => 'sendemail',
                'id'    => 'sendemail',
            ));
            $customer->setData('sendemail', '1');
            if (!Mage::app()->isSingleStoreMode()) {
                $fieldset->addField('sendemail_store_id', 'select', array(
                    'label' => $this->helper('Mage_Customer_Helper_Data')->__('Send From'),
                    'name' => 'sendemail_store_id',
                    'values' => Mage::getSingleton('Mage_Adminhtml_Model_System_Store')->getStoreValuesForForm()
                ));
            }
        }

        // make sendemail and sendmail_store_id disabled, if website_id has empty value
        $isSingleMode = Mage::app()->isSingleStoreMode();
        $sendEmailId = $isSingleMode ? 'sendemail' : 'sendemail_store_id';
        $sendEmail = $form->getElement($sendEmailId);

        $prefix = $form->getHtmlIdPrefix();
        if ($sendEmail) {
            $_disableStoreField = '';
            if (!$isSingleMode) {
                $_disableStoreField = "$('{$prefix}sendemail_store_id').disabled=(''==this.value || '0'==this.value);";
            }
            $sendEmail->setAfterElementHtml(
                '<script type="text/javascript">'
                . "
                $('{$prefix}website_id').disableSendemail = function() {
                    $('{$prefix}sendemail').disabled = ('' == this.value || '0' == this.value);".
                    $_disableStoreField
                ."}.bind($('{$prefix}website_id'));
                Event.observe('{$prefix}website_id', 'change', $('{$prefix}website_id').disableSendemail);
                $('{$prefix}website_id').disableSendemail();
                "
                . '</script>'
            );
        }

        if ($customer->isReadonly()) {
            foreach ($customer->getAttributes() as $attribute) {
                $element = $form->getElement($attribute->getAttributeCode());
                if ($element) {
                    $element->setReadonly(true, true);
                }
            }
        }

        $form->setValues($customer->getData());
        $this->setForm($form);
        return $this;
    }

    /**
     * Return predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Customer_Form_Element_File'),
            'image'     => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Customer_Form_Element_Image'),
            'boolean'   => Mage::getConfig()->getBlockClassName('Mage_Adminhtml_Block_Customer_Form_Element_Boolean'),
        );
    }
}
