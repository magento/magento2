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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account form block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
namespace Magento\Adminhtml\Block\Customer\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Account extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Disable Auto Group Change Attribute Name
     */
    const DISABLE_ATTRIBUTE_NAME = 'disable_auto_group_change';

    /**
     * @var \Magento\Customer\Model\FormFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Core\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Customer\Model\FormFactory $customerFactory
     * @param \Magento\Core\Model\System\Store $systemStore
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\FormFactory $customerFactory,
        \Magento\Core\Model\System\Store $systemStore,
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_customerFactory = $customerFactory;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
    }

    /**
     * Initialize form
     *
     * @return \Magento\Adminhtml\Block\Customer\Edit\Tab\Account
     */
    public function initForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('account');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => __('Account Information')
        ));

        $customer = $this->_coreRegistry->registry('current_customer');
        /** @var $customerForm \Magento\Customer\Model\Form */
        $customerForm = $this->_initCustomerForm($customer);
        $attributes = $this->_initCustomerAttributes($customerForm);
        $this->_setFieldset($attributes, $fieldset, array(self::DISABLE_ATTRIBUTE_NAME));

        $form->getElement('group_id')->setRenderer($this->getLayout()
            ->createBlock('Magento\Adminhtml\Block\Customer\Edit\Renderer\Attribute\Group')
            ->setDisableAutoGroupChangeAttribute($customerForm->getAttribute(self::DISABLE_ATTRIBUTE_NAME))
            ->setDisableAutoGroupChangeAttributeValue($customer->getData(self::DISABLE_ATTRIBUTE_NAME))
        );

        $this->_setCustomerWebsiteId($customer);
        $customerStoreId = $this->_getCustomerStoreId($customer);

        $prefixElement = $form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->helper('Magento\Customer\Helper\Data')->getNamePrefixOptions($customerStoreId);
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
            $suffixOptions = $this->helper('Magento\Customer\Helper\Data')->getNameSuffixOptions($customerStoreId);
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
            $this->_addEditCustomerFormFields($form, $fieldset, $customer);
        } else {
            $this->_addNewCustomerFormFields($form, $fieldset);
            $customer->setData('sendemail', '1');
        }

        $this->_disableSendEmailStoreForEmptyWebsite($form);
        $this->_handleReadOnlyCustomer($form, $customer);

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
            'file'      => 'Magento\Adminhtml\Block\Customer\Form\Element\File',
            'image'     => 'Magento\Adminhtml\Block\Customer\Form\Element\Image',
            'boolean'   => 'Magento\Adminhtml\Block\Customer\Form\Element\Boolean',
        );
    }

    /**
     * Initialize attribute set
     *
     * @param \Magento\Customer\Model\Form $customerFor
     * @return \Magento\Eav\Model\Entity\Attribute[]
     */
    protected function _initCustomerAttributes(\Magento\Customer\Model\Form $customerForm)
    {
        $attributes = $customerForm->getAttributes();
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $attributeLabel = __($attribute->getFrontend()->getLabel());
            $attribute->setFrontendLabel($attributeLabel);
            $attribute->unsIsVisible();
        }
        return $attributes;
    }

    /**
     * Initialize customer form
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return \Magento\Customer\Model\Form $customerForm
     */
    protected function _initCustomerForm(\Magento\Customer\Model\Customer $customer)
    {
        /** @var $customerForm \Magento\Customer\Model\Form */
        $customerForm = $this->_customerFactory->create();
        $customerForm->setEntity($customer)
            ->setFormCode('adminhtml_customer')
            ->initDefaultValues();

        return $customerForm;
    }

    /**
     * Handle Read-Only customer
     *
     * @param \Magento\Data\Form $form
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function _handleReadOnlyCustomer($form, $customer)
    {
        if (!$customer->isReadonly()) {
            return;
        }
        foreach ($customer->getAttributes() as $attribute) {
            $element = $form->getElement($attribute->getAttributeCode());
            if ($element) {
                $element->setReadonly(true, true);
            }
        }
    }

    /**
     * Make sendemail or sendmail_store_id disabled if website_id has an empty value
     *
     * @param \Magento\Data\Form $form
     */
    protected function _disableSendEmailStoreForEmptyWebsite(\Magento\Data\Form $form)
    {
        $sendEmailId = $this->_storeManager->isSingleStoreMode() ? 'sendemail' : 'sendemail_store_id';
        $sendEmail = $form->getElement($sendEmailId);

        $prefix = $form->getHtmlIdPrefix();
        if ($sendEmail) {
            $_disableStoreField = '';
            if (!$this->_storeManager->isSingleStoreMode()) {
                $_disableStoreField = "$('{$prefix}sendemail_store_id').disabled=(''==this.value || '0'==this.value);";
            }
            $sendEmail->setAfterElementHtml(
                '<script type="text/javascript">'
                . "
                document.observe('dom:loaded', function(){
                    $('{$prefix}website_id').disableSendemail = function() {
                        $('{$prefix}sendemail').disabled = ('' == this.value || '0' == this.value);".
                        $_disableStoreField
                    ."\n}.bind($('{$prefix}website_id'));
                    Event.observe('{$prefix}website_id', 'change', $('{$prefix}website_id').disableSendemail);
                    $('{$prefix}website_id').disableSendemail();
                });
                "
                . '</script>'
            );
        }
    }

    /**
     * Create New Customer form fields
     *
     * @param \Magento\Data\Form $form
     * @param \Magento\Data\Form\Element\Fieldset $fieldset
     */
    protected function _addNewCustomerFormFields($form, $fieldset)
    {
        $fieldset->removeField('created_in');

        // Prepare send welcome email checkbox
        $fieldset->addField('sendemail', 'checkbox', array(
            'label' => __('Send Welcome Email'),
            'name'  => 'sendemail',
            'id'    => 'sendemail',
        ));
        if (!$this->_storeManager->isSingleStoreMode()) {
            $form->getElement('website_id')->addClass('validate-website-has-store');

            $websites = array();
            foreach ($this->_storeManager->getWebsites(true) as $website) {
                $websites[$website->getId()] = !is_null($website->getDefaultStore());
            }
            $prefix = $form->getHtmlIdPrefix();

            $note = __('Please select a website which contains store view');
            $form->getElement('website_id')->setAfterElementHtml(
                '<script type="text/javascript">'
                . "
                var {$prefix}_websites = " . $this->_coreData->jsonEncode($websites) . ";
                jQuery.validator.addMethod('validate-website-has-store', function(v, elem){
                        return {$prefix}_websites[elem.value] == true;
                    },
                    '" . $note . "'
                );
                Element.observe('{$prefix}website_id', 'change', function(){
                    jQuery.validator.validateElement('#{$prefix}website_id');
                }.bind($('{$prefix}website_id')));
                "
                . '</script>'
            );
            $renderer = $this->getLayout()
                ->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $form->getElement('website_id')->setRenderer($renderer);

            $fieldset->addField('sendemail_store_id', 'select', array(
                'label' => __('Send From'),
                'name' => 'sendemail_store_id',
                'values' => $this->_systemStore->getStoreValuesForForm()
            ));
        } else {
            $fieldset->removeField('website_id');
            $fieldset->addField('website_id', 'hidden', array(
                'name' => 'website_id'
            ));
        }
    }

    /**
     * Edit/View Existing Customer form fields
     *
     * @param \Magento\Data\Form $form
     * @param \Magento\Data\Form\Element\Fieldset $fieldset
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function _addEditCustomerFormFields($form, $fieldset, $customer)
    {
        $form->getElement('created_in')->setDisabled('disabled');
        if (!$this->_storeManager->isSingleStoreMode()) {
            $form->getElement('website_id')->setDisabled('disabled');
            $renderer = $this->getLayout()
                ->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $form->getElement('website_id')->setRenderer($renderer);
        } else {
            $fieldset->removeField('website_id');
        }

        if ($customer->isReadonly()) {
            return;
        }

        // Prepare customer confirmation control (only for existing customers)
        $confirmationKey = $customer->getConfirmation();
        if ($confirmationKey || $customer->isConfirmationRequired()) {
            $confirmationAttr = $customer->getAttribute('confirmation');
            if (!$confirmationKey) {
                $confirmationKey = $customer->getRandomConfirmationKey();
            }

            $element = $fieldset->addField('confirmation', 'select', array(
                'name'  => 'confirmation',
                'label' => __($confirmationAttr->getFrontendLabel()),
            ));
            $element->setEntityAttribute($confirmationAttr);
            $element->setValues(array(
                '' => 'Confirmed',
                $confirmationKey => 'Not confirmed'
            ));

            // Prepare send welcome email checkbox if customer is not confirmed
            // no need to add it, if website ID is empty
            if ($customer->getConfirmation() && $customer->getWebsiteId()) {
                $fieldset->addField('sendemail', 'checkbox', array(
                    'name'  => 'sendemail',
                    'label' => __('Send Welcome Email after Confirmation')
                ));
                $customer->setData('sendemail', '1');
            }
        }
    }

    /**
     * Add Password management fieldset
     *
     * @param \Magento\Data\Form $form
     * @param string $fieldLabel
     * @param boolean $isNew whether we set initial password or change existing one
     */
    protected function _addPasswordManagementFieldset($form, $fieldLabel, $isNew)
    {
        // Add password management fieldset
        $newFieldset = $form->addFieldset(
            'password_fieldset',
            array('legend' => __('Password Management'))
        );
        if ($isNew) {
            // New customer password for existing customer
            $elementId = 'new_password';
            $elementClass = 'validate-new-password';
        } else {
            // Password field for newly generated customer
            $elementId = 'password';
            $elementClass = 'input-text required-entry validate-password';
        }
        $field = $newFieldset->addField($elementId, 'text',
            array(
                'label' => __($fieldLabel),
                'name'  => $elementId,
                'class' => $elementClass,
                'required' => !$isNew,
            )
        );
        $field->setRenderer(
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Customer\Edit\Renderer\Newpass')
        );
    }

    /**
     * Get Customer Store Id
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return int|null
     */
    protected function _getCustomerStoreId(\Magento\Customer\Model\Customer $customer)
    {
        $customerStoreId = null;
        if ($customer->getId()) {
            $customerStoreId = $this->_storeManager->getWebsite($customer->getWebsiteId())
                ->getDefaultStore()
                ->getId();
        }
        return $customerStoreId;
    }

    /**
     * Set Customer Website Id in Single Store Mode
     *
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function _setCustomerWebsiteId(\Magento\Customer\Model\Customer $customer)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            $customer->setWebsiteId($this->_storeManager->getStore(true)->getWebsiteId());
        }
    }
}
