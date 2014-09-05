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
use \Magento\Framework\Service\SimpleDataObjectConverter;

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
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
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
     * @var \Magento\Customer\Model\Metadata\Form
     */
    protected $_customerForm;

    /**
     * @var \Magento\Customer\Service\V1\Data\Customer
     */
    protected $_customerDataObject;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Store\Model\System\Store $systemStore
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
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Store\Model\System\Store $systemStore,
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
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('account');

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Account Information')));
        $accountData = $this->_customizeFieldset($fieldset);

        $form->setValues($accountData);
        $this->setForm($form);
        return $this;
    }

    /**
     * Customize fieldset elements
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return array
     */
    protected function _customizeFieldset($fieldset)
    {
        $attributes = $this->_initCustomerAttributes();
        $this->_setFieldset($attributes, $fieldset, array(self::DISABLE_ATTRIBUTE_NAME));
        $form = $fieldset->getForm();
        $groupElement = $form->getElement(
            'group_id'
        );
        $groupElement->setRenderer(
            $this->getLayout()->createBlock(
                'Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group'
            )->setDisableAutoGroupChangeAttribute(
                $this->_getCustomerForm()->getAttribute(self::DISABLE_ATTRIBUTE_NAME)
            )->setDisableAutoGroupChangeAttributeValue(
                $this->_getCustomerDataObject()->getCustomAttribute(self::DISABLE_ATTRIBUTE_NAME) ?
                $this->_getCustomerDataObject()->getCustomAttribute(self::DISABLE_ATTRIBUTE_NAME)->getValue() : null
            )
        );

        $this->_checkElementType('prefix', $fieldset);
        $this->_checkElementType('suffix', $fieldset);

        $fieldset->getForm()->getElement('website_id')->addClass('validate-website-has-store');
        $renderer = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
        );
        $form->getElement('website_id')->setRenderer($renderer);

        $accountData = SimpleDataObjectConverter::toFlatArray($this->_getCustomerDataObject());
        if ($this->_getCustomerDataObject()->getId()) {
            $customerFormFields = $this->_addEditCustomerFormFields($fieldset);
        } else {
            $customerFormFields = $this->_addNewCustomerFormFields($fieldset);
        }

        $this->_handleReadOnlyCustomer($form, $this->_getCustomerDataObject()->getId(), $attributes);

        return array_merge($customerFormFields, $accountData);
    }

    /**
     * Check if type of Prefix and Suffix elements should be changed from text to select and change it if need.
     *
     * @param string $elementName
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return null
     */
    protected function _checkElementType($elementName, $fieldset)
    {
        $possibleElements = ['prefix', 'suffix'];
        if (!in_array($elementName, $possibleElements)) {
            return;
        }
        $element = $fieldset->getForm()->getElement($elementName);
        if ($element) {
            if ($elementName == 'prefix') {
                $options = $this->_customerHelper->getNamePrefixOptions($this->_getCustomerDataObject()->getStoreId());
                $prevSibling = $fieldset->getForm()->getElement('group_id')->getId();
            }
            if ($elementName == 'suffix') {
                $options = $this->_customerHelper->getNameSuffixOptions($this->_getCustomerDataObject()->getStoreId());
                $prevSibling = $fieldset->getForm()->getElement('lastname')->getId();
            }

            if (!empty($options)) {
                $fieldset->removeField($element->getId());
                $elementField = $fieldset->addField(
                    $element->getId(),
                    'select',
                    $element->getData(),
                    $prevSibling
                );
                $elementField->setValues($options);
            }
        }
    }

    /**
     * Obtain customer data from session and create customer object
     *
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    protected function _getCustomerDataObject()
    {
        if (is_null($this->_customerDataObject)) {
            $customerData = $this->_backendSession->getCustomerData();
            $accountData = isset($customerData['account']) ? $customerData['account'] : array();
            $this->_customerDataObject = $this->_customerBuilder->populateWithArray($accountData)->create();
        }
        return $this->_customerDataObject;
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
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
     */
    protected function _initCustomerAttributes()
    {
        $attributes = $this->_getCustomerForm()->getAttributes();
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
     * @return \Magento\Customer\Model\Metadata\Form $customerForm
     */
    protected function _getCustomerForm()
    {
        if (is_null($this->_customerForm)) {
            $this->_customerForm = $this->_customerFormFactory->create(
                'customer',
                'adminhtml_customer',
                SimpleDataObjectConverter::toFlatArray($this->_getCustomerDataObject())
            );
        }
        return $this->_customerForm;
    }

    /**
     * Handle Read-Only customer
     *
     * @param \Magento\Framework\Data\Form $form
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
     * Create New Customer form fields
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return array
     */
    protected function _addNewCustomerFormFields($fieldset)
    {
        $fieldset->removeField('created_in');

        // Prepare send welcome email checkbox
        $fieldset->addField(
            'sendemail',
            'checkbox',
            array('label' => __('Send Welcome Email'), 'name' => 'sendemail', 'id' => 'sendemail')
        );
        $renderer = $this->getLayout()->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Sendemail'
        );
        $renderer->setForm($fieldset->getForm());
        $fieldset->getForm()->getElement('sendemail')->setRenderer($renderer);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'sendemail_store_id',
                'select',
                array(
                    'label' => __('Send From'),
                    'name' => 'sendemail_store_id',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                )
            );
        }

        return array('sendemail' => '1');
    }

    /**
     * Edit/View Existing Customer form fields
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return string[] Values to set on the form
     */
    protected function _addEditCustomerFormFields($fieldset)
    {
        $fieldset->getForm()->getElement('created_in')->setDisabled('disabled');
        $fieldset->getForm()->getElement('website_id')->setDisabled('disabled');
        $customerData = $this->_getCustomerDataObject();
        if ($customerData->getId() &&
            !$this->_customerAccountService->canModify($customerData->getId())
        ) {
            return array();
        }


        // Prepare customer confirmation control (only for existing customers)
        $confirmationStatus = $this->_customerAccountService->getConfirmationStatus($customerData->getId());
        $confirmationKey = $customerData->getConfirmation();
        if ($confirmationStatus != CustomerAccountServiceInterface::ACCOUNT_CONFIRMED) {
            $confirmationAttr = $this->_customerMetadataService->getAttributeMetadata('confirmation');
            if (!$confirmationKey) {
                $confirmationKey = $this->_getRandomConfirmationKey();
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
            if ($customerData->getConfirmation() && $customerData->getWebsiteId()) {
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
