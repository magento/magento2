<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;

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
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_metadataFormFactory;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * Group Management
     *
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param array $data
     * @param GroupManagementInterface|null $groupManagement
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
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
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Account Information');
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Customer\Model\Metadata\Form $customerForm */
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
                $element->setRequired(1);
                $element->setClass('validate-email admin__control-text');
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
        try {
            $customer = $this->customerRepository->getById($this->getCustomerId());
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\Exception $e) {
            /** If customer does not exist do nothing. */
        }
        $data = isset($customer)
            ? $this->_extensibleDataObjectConverter->toFlatArray(
                $customer,
                [],
                \Magento\Customer\Api\Data\CustomerInterface::class
            )
            : [];
        foreach ($this->getQuote()->getData() as $key => $value) {
            if (strpos($key, 'customer_') === 0) {
                $data[substr($key, 9)] = $value;
            }
        }

        if (array_key_exists('group_id', $data) && empty($data['group_id'])) {
            $data['group_id'] = $this->groupManagement->getDefaultGroup($this->getQuote()->getStoreId())->getId();
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
}
