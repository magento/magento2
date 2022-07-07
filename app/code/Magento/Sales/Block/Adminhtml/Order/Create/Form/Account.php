<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Customer\Model\Metadata\FormFactory as MetadataFormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Store\Model\ScopeInterface;

/**
 * Create order account form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Account extends AbstractForm
{
    /**
     * Metadata form factory
     *
     * @var MetadataFormFactory
     */
    protected $_metadataFormFactory;

    /**
     * Customer repository
     *
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    private const XML_PATH_EMAIL_REQUIRED_CREATE_ORDER = 'customer/create_account/email_required_create_order';

    /**
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param FormFactory $formFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param MetadataFormFactory $metadataFormFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param array $data
     * @param GroupManagementInterface|null $groupManagement
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        FormFactory $formFactory,
        DataObjectProcessor $dataObjectProcessor,
        MetadataFormFactory $metadataFormFactory,
        CustomerRepositoryInterface $customerRepository,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        array $data = [],
        ?GroupManagementInterface $groupManagement = null
    ) {
        $this->_metadataFormFactory = $metadataFormFactory;
        $this->customerRepository = $customerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->groupManagement = $groupManagement ?: ObjectManager::getInstance()->get(GroupManagementInterface::class);
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
    }

    /**
     * Group Management
     *
     * @var GroupManagementInterface
     */
    private $groupManagement;

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
     * @return Phrase
     */
    public function getHeaderText()
    {
        return __('Account Information');
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareForm()
    {
        /** @var Form $customerForm */
        $customerForm = $this->_metadataFormFactory->create('customer', 'adminhtml_checkout');

        // prepare customer attributes to show
        $attributes = [];

        // add system required attributes
        foreach ($customerForm->getSystemAttributes() as $attribute) {
            if ($attribute->isRequired()) {
                $attributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        if ($this->getQuote()->getCustomerIsGuest()) {
            unset($attributes['group_id']);
        }

        // add user defined attributes
        foreach ($customerForm->getUserAttributes() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        $fieldset = $this->_form->addFieldset('main', []);

        $this->_addAttributesToForm($attributes, $fieldset);

        $this->_form->addFieldNameSuffix('order[account]');
        $this->_form->setValues($this->extractValuesFromAttributes($attributes));

        return $this;
    }

    /**
     * Add additional data to form element
     *
     * @param AbstractElement $element
     * @return $this
     */
    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        switch ($element->getId()) {
            case 'email':
                $element->setRequired($this->isEmailRequiredToCreateOrder());
                $element->setClass('validate-email admin__control-text');
                break;
        }
        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFormValues()
    {
        try {
            $customer = $this->customerRepository->getById($this->getCustomerId());
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\Exception $e) {
            $data = [];
        }
        $data = isset($customer)
            ? $this->_extensibleDataObjectConverter->toFlatArray(
                $customer,
                [],
                CustomerInterface::class
            )
            : [];
        foreach ($this->getQuote()->getData() as $key => $value) {
            if (strpos($key, 'customer_') === 0) {
                $data[substr($key, 9)] = $value;
            }
        }

        if (array_key_exists('group_id', $data) && empty($data['group_id'])) {
            $data['group_id'] = $this->getSelectedGroupId();
        }

        if ($this->getQuote()->getCustomerEmail()) {
            $data['email'] = $this->getQuote()->getCustomerEmail();
        }

        return $data;
    }

    /**
     * Extract the form values from attributes.
     *
     * @param array $attributes
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function extractValuesFromAttributes(array $attributes): array
    {
        $formValues = $this->getFormValues();
        foreach ($attributes as $code => $attribute) {
            $defaultValue = $attribute->getDefaultValue();
            if (isset($defaultValue) && !isset($formValues[$code])) {
                $formValues[$code] = $defaultValue;
            }
        }

        return $formValues;
    }

    /**
     * Retrieve email is required field for admin order creation
     *
     * @return bool
     */
    private function isEmailRequiredToCreateOrder()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_REQUIRED_CREATE_ORDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve selected group id
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getSelectedGroupId(): string
    {
        $selectedGroupId = $this->groupManagement->getDefaultGroup($this->getQuote()->getStoreId())->getId();
        $orderDetails = $this->getRequest()->getParam('order');
        if (!empty($orderDetails) && !empty($orderDetails['account']['group_id'])) {
            $selectedGroupId = $orderDetails['account']['group_id'];
        }
        return $selectedGroupId;
    }
}
