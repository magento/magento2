<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Eav\Model\AttributeDataFactory;

/**
 * Order create address form
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
{
    /**
     * Customer form factory
     *
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_customerFormFactory;

    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Directory helper
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * Customer options
     *
     * @var \Magento\Customer\Model\Options
     */
    protected $options;

    /**
     * Address service
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressService;

    /**
     * Address helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_addressHelper;

    /**
     * Search criteria builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Filter builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    private $countriesCollection;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $backendQuoteSession;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Customer\Model\Options $options
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressService
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Model\Options $options,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressService,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = []
    ) {
        $this->options = $options;
        $this->directoryHelper = $directoryHelper;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_customerFormFactory = $customerFormFactory;
        $this->_addressHelper = $addressHelper;
        $this->addressService = $addressService;
        $this->searchCriteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->addressMapper = $addressMapper;
        $this->backendQuoteSession = $sessionQuote;
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
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve current customer address DATA collection.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getAddressCollection()
    {
        if ($this->getCustomerId()) {
            $filter = $this->filterBuilder
                ->setField('parent_id')
                ->setValue($this->getCustomerId())
                ->setConditionType('eq')
                ->create();
            $this->searchCriteriaBuilder->addFilters([$filter]);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $result = $this->addressService->getList($searchCriteria);
            return $result->getItems();
        }
        return [];
    }

    /**
     * Return Customer Address Collection as JSON
     *
     * @return string
     */
    public function getAddressCollectionJson()
    {
        $defaultCountryId = $this->directoryHelper->getDefaultCountry($this->getStore());
        $emptyAddressForm = $this->_customerFormFactory->create(
            'customer_address',
            'adminhtml_customer_address',
            [AddressInterface::COUNTRY_ID => $defaultCountryId]
        );
        $data = [0 => $emptyAddressForm->outputData(AttributeDataFactory::OUTPUT_FORMAT_JSON)];
        foreach ($this->getAddressCollection() as $address) {
            $addressForm = $this->_customerFormFactory->create(
                'customer_address',
                'adminhtml_customer_address',
                $this->addressMapper->toFlatArray($address),
                false,
                false
            );
            $data[$address->getId()] = $addressForm->outputData(
                AttributeDataFactory::OUTPUT_FORMAT_JSON
            );
        }

        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareForm()
    {
        $storeId = $this->getCreateOrderModel()
            ->getSession()
            ->getStoreId();
        $this->_storeManager->setCurrentStore($storeId);

        $fieldset = $this->_form->addFieldset('main', ['no_container' => true]);

        $addressForm = $this->_customerFormFactory->create('customer_address', 'adminhtml_customer_address');
        $attributes = $addressForm->getAttributes();
        $this->_addAttributesToForm($attributes, $fieldset);

        $prefixElement = $this->_form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->options->getNamePrefixOptions($this->getStore());
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField($prefixElement->getId(), 'select', $prefixElement->getData(), '^');
                $prefixField->setValues($prefixOptions);
                if ($this->getAddressId()) {
                    $prefixField->addElementValues($this->getAddress()->getPrefix());
                }
            }
        }

        $suffixElement = $this->_form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->options->getNameSuffixOptions($this->getStore());
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField(
                    $suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $this->_form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
                if ($this->getAddressId()) {
                    $suffixField->addElementValues($this->getAddress()->getSuffix());
                }
            }
        }

        $regionElement = $this->_form->getElement('region_id');
        if ($regionElement) {
            $regionElement->setNoDisplay(true);
        }

        $this->_form->setValues($this->getFormValues());

        $countryElement = $this->_form->getElement('country_id');

        $this->processCountryOptions($countryElement);

        if ($countryElement->getValue()) {
            $countryId = $countryElement->getValue();
            $countryElement->setValue(null);
            foreach ($countryElement->getValues() as $country) {
                if ($country['value'] == $countryId) {
                    $countryElement->setValue($countryId);
                }
            }
        }
        if ($countryElement->getValue() === null) {
            $countryElement->setValue(
                $this->directoryHelper->getDefaultCountry($this->getStore())
            );
        }
        // Set custom renderer for VAT field if needed
        $vatIdElement = $this->_form->getElement('vat_id');
        if ($vatIdElement && $this->getDisplayVatValidationButton() !== false) {
            $vatIdElement->setRenderer(
                $this->getLayout()->createBlock(
                    \Magento\Customer\Block\Adminhtml\Sales\Order\Address\Form\Renderer\Vat::class
                )->setJsVariablePrefix(
                    $this->getJsVariablePrefix()
                )
            );
        }

        return $this;
    }

    /**
     * Process country options.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $countryElement
     * @return void
     */
    private function processCountryOptions(\Magento\Framework\Data\Form\Element\AbstractElement $countryElement)
    {
        $storeId = $this->getAddressStoreId();
        $options = $this->getCountriesCollection()
            ->loadByStore($storeId)
            ->toOptionArray();

        $countryElement->setValues($options);
    }

    /**
     * Retrieve Directory Countries collection
     *
     * @deprecated 100.1.3
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    private function getCountriesCollection()
    {
        if (!$this->countriesCollection) {
            $this->countriesCollection = ObjectManager::getInstance()
                ->get(\Magento\Directory\Model\ResourceModel\Country\Collection::class);
        }

        return $this->countriesCollection;
    }

    /**
     * Retrieve Backend Quote Session
     *
     * @deprecated 100.1.3
     * @return Quote
     */
    private function getBackendQuoteSession()
    {
        if (!$this->backendQuoteSession) {
            $this->backendQuoteSession = ObjectManager::getInstance()->get(Quote::class);
        }

        return $this->backendQuoteSession;
    }

    /**
     * Add additional data to form element
     *
     * @param AbstractElement $element
     * @return $this
     */
    protected function _addAdditionalFormElementData(AbstractElement $element)
    {
        if ($element->getId() == 'region_id') {
            $element->setNoDisplay(true);
        }
        return $this;
    }

    /**
     * Return customer address id
     *
     * @return false
     */
    public function getAddressId()
    {
        return false;
    }

    /**
     * Represent customer address in 'online' format.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressAsString(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $formatTypeRenderer = $this->_addressHelper->getFormatTypeRenderer('oneline');
        $result = '';
        if ($formatTypeRenderer) {
            $result = $formatTypeRenderer->renderArray($this->addressMapper->toFlatArray($address));
        }

        return $this->escapeHtml($result);
    }

    /**
     * Return address store id.
     *
     * @return int
     */
    protected function getAddressStoreId()
    {
        return $this->getBackendQuoteSession()->getStoreId();
    }
}
