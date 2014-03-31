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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;

/**
 * Customer account form block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Account extends GenericMetadata
{
    /**
     * Disable Auto Group Change Attribute Name
     */
    const DISABLE_ATTRIBUTE_NAME = 'disable_auto_group_change';

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_customerFormFactory;

    /**
     * @var \Magento\Core\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Customer\Helper\Data
     */
    protected $_customerHelper;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface
     */
    protected $_customerMetadataService;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    protected $_customerBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Core\Model\System\Store $systemStore
     * @param \Magento\Customer\Helper\Data $customerHelper
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $customerMetadataService
     * @param \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Core\Model\System\Store $systemStore,
        \Magento\Customer\Helper\Data $customerHelper,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $customerMetadataService,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        array $data = array()
    ) {
        $this->_customerHelper = $customerHelper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_systemStore = $systemStore;
        $this->_customerFormFactory = $customerFormFactory;
        $this->_customerAccountService = $customerAccountService;
        $this->_customerMetadataService = $customerMetadataService;
        $this->_customerBuilder = $customerBuilder;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Initialize form
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function initForm()
    {
        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('account');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Account Information')));

        $customerData = $this->_backendSession->getCustomerData();
        $customerId = isset($customerData['customer_id']) ? $customerData['customer_id'] : false;
        $accountData = isset($customerData['account']) ? $customerData['account'] : array();
        $customerDataObject = $this->_customerBuilder->populateWithArray($accountData)->create();

        $customerForm = $this->_initCustomerForm($customerDataObject);
        $attributes = $this->_initCustomerAttributes($customerForm);
        $this->_setFieldset($attributes, $fieldset, array(self::DISABLE_ATTRIBUTE_NAME));

        $form->getElement(
            'group_id'
        )->setRenderer(
            $this->getLayout()->createBlock(
                'Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group'
            )->setDisableAutoGroupChangeAttribute(
                $customerForm->getAttribute(self::DISABLE_ATTRIBUTE_NAME)
            )->setDisableAutoGroupChangeAttributeValue(
                $customerDataObject->getCustomAttribute(self::DISABLE_ATTRIBUTE_NAME)
            )
        );

        $customerStoreId = $customerDataObject->getStoreId();

        $prefixElement = $form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->_customerHelper->getNamePrefixOptions($customerStoreId);
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField(
                    $prefixElement->getId(),
                    'select',
                    $prefixElement->getData(),
                    $form->getElement('group_id')->getId()
                );
                $prefixField->setValues($prefixOptions);
                if ($customerId) {
                    $prefixField->addElementValues($customerDataObject->getPrefix());
                }
            }
        }

        $suffixElement = $form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->_customerHelper->getNameSuffixOptions($customerStoreId);
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField(
                    $suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
                if ($customerId) {
                    $suffixField->addElementValues($customerDataObject->getSuffix());
                }
            }
        }

        if ($customerId) {
            $accountData = array_merge(
                $this->_addEditCustomerFormFields($form, $fieldset, $customerDataObject),
                $accountData
            );
        } else {
            $this->_addNewCustomerFormFields($form, $fieldset);
            $accountData['sendemail'] = '1';
        }

        $this->_disableSendEmailStoreForEmptyWebsite($form);
        $this->_handleReadOnlyCustomer($form, $customerId, $attributes);

        $form->setValues($accountData);
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
            'file' => 'Magento\Customer\Block\Adminhtml\Form\Element\File',
            'image' => 'Magento\Customer\Block\Adminhtml\Form\Element\Image',
            'boolean' => 'Magento\Customer\Block\Adminhtml\Form\Element\Boolean'
        );
    }

    /**
     * Initialize attribute set.
     *
     * @param \Magento\Customer\Model\Metadata\Form $customerForm
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
     */
    protected function _initCustomerAttributes(\Magento\Customer\Model\Metadata\Form $customerForm)
    {
        $attributes = $customerForm->getAttributes();

        foreach ($attributes as $key => $attribute) {
            if ($attribute->getAttributeCode() == 'created_at') {
                unset($attributes[$key]);
            }
        }
        return $attributes;
    }

    /**
     * Initialize customer form
     *
     * @param \Magento\Customer\Service\V1\Data\Customer $customer
     * @return \Magento\Customer\Model\Metadata\Form $customerForm
     */
    protected function _initCustomerForm(\Magento\Customer\Service\V1\Data\Customer $customer)
    {
        return $this->_customerFormFactory->create(
            'customer',
            'adminhtml_customer',
            \Magento\Service\DataObjectConverter::toFlatArray($customer)
        );
    }

    /**
     * Handle Read-Only customer
     *
     * @param \Magento\Data\Form $form
     * @param int $customerId
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[] $attributes
     * @return void
     */
    protected function _handleReadOnlyCustomer($form, $customerId, $attributes)
    {
        if ($customerId && !$this->_customerAccountService->canModify($customerId)) {
            foreach ($attributes as $attribute) {
                $element = $form->getElement($attribute->getAttributeCode());
                if ($element) {
                    $element->setReadonly(true, true);
                }
            }
        }
    }

    /**
     * Make sendemail or sendmail_store_id disabled if website_id has an empty value
     *
     * @param \Magento\Data\Form $form
     * @return void
     */
    protected function _disableSendEmailStoreForEmptyWebsite(\Magento\Data\Form $form)
    {
        $sendEmailId = $this->_storeManager->isSingleStoreMode() ? 'sendemail' : 'sendemail_store_id';
        $sendEmail = $form->getElement($sendEmailId);

        $prefix = $form->getHtmlIdPrefix();
        if ($sendEmail) {
            $_disableStoreField = '';
            if (!$this->_storeManager->isSingleStoreMode()) {
                $_disableStoreField = "\$('{$prefix}sendemail_store_id').disabled=(''==this.value || '0'==this.value);";
            }
            $sendEmail->setAfterElementHtml(
                '<script type="text/javascript">' .
                "\n                document.observe('dom:loaded', function()".
                "{\n                    \$('{$prefix}website_id').disableSendemail = function() ".
                "{\n                        \$('{$prefix}sendemail').disabled = ('' == this.value || ".
                "'0' == this.value);" .
                $_disableStoreField .
                "\n}.bind(\$('{$prefix}website_id'));\n                    ".
                "Event.observe('{$prefix}website_id', 'change', \$('{$prefix}website_id').disableSendemail);".
                "\n                    \$('{$prefix}website_id').disableSendemail();\n                });".
                "\n                " .
                '</script>'
            );
        }
    }

    /**
     * Create New Customer form fields
     *
     * @param \Magento\Data\Form $form
     * @param \Magento\Data\Form\Element\Fieldset $fieldset
     * @return void
     */
    protected function _addNewCustomerFormFields($form, $fieldset)
    {
        $fieldset->removeField('created_in');

        // Prepare send welcome email checkbox
        $fieldset->addField(
            'sendemail',
            'checkbox',
            array('label' => __('Send Welcome Email'), 'name' => 'sendemail', 'id' => 'sendemail')
        );
        if (!$this->_storeManager->isSingleStoreMode()) {
            $form->getElement('website_id')->addClass('validate-website-has-store');

            $websites = array();
            foreach ($this->_storeManager->getWebsites(true) as $website) {
                $websites[$website->getId()] = !is_null($website->getDefaultStore());
            }
            $prefix = $form->getHtmlIdPrefix();

            $note = __('Please select a website which contains store view');
            $form->getElement(
                'website_id'
            )->setAfterElementJs(
                '<script type="text/javascript">' .
                "\n                var {$prefix}_websites = " .
                $this->_jsonEncoder->encode(
                    $websites
                ) .
                ";\n                jQuery.validator.addMethod('validate-website-has-store', function(v, elem)".
                "{\n                        return {$prefix}_websites[elem.value] == true;\n                    },".
                "\n                    '" .
                $note .
                "'\n                );\n                ".
                "Element.observe('{$prefix}website_id', 'change', function()".
                "{\n                    jQuery.validator.validateElement('#{$prefix}website_id');".
                "\n                }.bind(\$('{$prefix}website_id')));\n                " .
                '</script>'
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $form->getElement('website_id')->setRenderer($renderer);

            $fieldset->addField(
                'sendemail_store_id',
                'select',
                array(
                    'label' => __('Send From'),
                    'name' => 'sendemail_store_id',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                )
            );
        } else {
            $fieldset->removeField('website_id');
            $fieldset->addField('website_id', 'hidden', array('name' => 'website_id'));
        }
    }

    /**
     * Edit/View Existing Customer form fields
     *
     * @param \Magento\Data\Form $form
     * @param \Magento\Data\Form\Element\Fieldset $fieldset
     * @param \Magento\Customer\Service\V1\Data\Customer $customerDataObject
     * @return string[] Values to set on the form
     */
    protected function _addEditCustomerFormFields($form, $fieldset, $customerDataObject)
    {
        $form->getElement('created_in')->setDisabled('disabled');
        if (!$this->_storeManager->isSingleStoreMode()) {
            $form->getElement('website_id')->setDisabled('disabled');
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $form->getElement('website_id')->setRenderer($renderer);
        } else {
            $fieldset->removeField('website_id');
        }

        if ($customerDataObject->getId() && !$this->_customerAccountService->canModify($customerDataObject->getId())) {
            return array();
        }


        // Prepare customer confirmation control (only for existing customers)
        $confirmationStatus = $this->_customerAccountService->getConfirmationStatus($customerDataObject->getId());
        $confirmationKey = $customerDataObject->getConfirmation();
        if ($confirmationStatus != CustomerAccountServiceInterface::ACCOUNT_CONFIRMED) {
            $confirmationAttr = $this->_customerMetadataService->getCustomerAttributeMetadata('confirmation');
            if (!$confirmationKey) {
                $confirmationKey = $this->getRandomConfirmationKey();
            }

            $element = $fieldset->addField(
                'confirmation',
                'select',
                array('name' => 'confirmation', 'label' => __($confirmationAttr->getFrontendLabel()))
            );
            $element->setEntityAttribute($confirmationAttr);
            $element->setValues(array('' => 'Confirmed', $confirmationKey => 'Not confirmed'));

            // Prepare send welcome email checkbox if customer is not confirmed
            // no need to add it, if website ID is empty
            if ($customerDataObject->getConfirmation() && $customerDataObject->getWebsiteId()) {
                $fieldset->addField(
                    'sendemail',
                    'checkbox',
                    array('name' => 'sendemail', 'label' => __('Send Welcome Email after Confirmation'))
                );
                return array('sendemail' => '1');
            }
        }
        return array();
    }

    /**
     * Called when account needs confirmation and does not have a confirmation key.
     *
     * @return string confirmation key
     */
    protected function _getRandomConfirmationKey()
    {
        return md5(uniqid());
    }
}
