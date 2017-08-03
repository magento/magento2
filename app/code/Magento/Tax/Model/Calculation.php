<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Customer\Api\Data\CustomerInterface as CustomerDataObject;
use Magento\Customer\Api\Data\RegionInterface as AddressRegion;
use Magento\Customer\Api\GroupManagementInterface as CustomerGroupManagement;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Tax\Api\TaxClassRepositoryInterface;

/**
 * Tax Calculation Model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Calculation extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Identifier constant for Tax calculation before discount excluding TAX
     */
    const CALC_TAX_BEFORE_DISCOUNT_ON_EXCL = '0_0';

    /**
     * Identifier constant for Tax calculation before discount including TAX
     */
    const CALC_TAX_BEFORE_DISCOUNT_ON_INCL = '0_1';

    /**
     * Identifier constant for Tax calculation after discount excluding TAX
     */
    const CALC_TAX_AFTER_DISCOUNT_ON_EXCL = '1_0';

    /**
     * Identifier constant for Tax calculation after discount including TAX
     */
    const CALC_TAX_AFTER_DISCOUNT_ON_INCL = '1_1';

    /**
     * Identifier constant for unit based calculation
     */
    const CALC_UNIT_BASE = 'UNIT_BASE_CALCULATION';

    /**
     * Identifier constant for row based calculation
     */
    const CALC_ROW_BASE = 'ROW_BASE_CALCULATION';

    /**
     * Identifier constant for total based calculation
     */
    const CALC_TOTAL_BASE = 'TOTAL_BASE_CALCULATION';

    /**
     * Identifier constant for unit based calculation
     *
     * @var array
     * @since 2.0.0
     */
    protected $_rates = [];

    /**
     * Identifier constant for row based calculation
     *
     * @var array
     * @since 2.0.0
     */
    protected $_ctc = [];

    /**
     * Identifier constant for total based calculation
     *
     * @var array
     * @since 2.0.0
     */
    protected $_ptc = [];

    /**
     * Cache to hold the rates
     *
     * @var array
     * @since 2.0.0
     */
    protected $_rateCache = [];

    /**
     * Store the rate calculation process
     *
     * @var array
     * @since 2.0.0
     */
    protected $_rateCalculationProcess = [];

    /**
     * Hold the customer
     *
     * @var CustomerDataObject|bool
     * @since 2.0.0
     */
    protected $_customer;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_defaultCustomerTaxClass;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     * @since 2.0.0
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory
     * @since 2.0.0
     */
    protected $_classesFactory;

    /**
     * Tax configuration object
     *
     * @var Config
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var CustomerAccountManagement
     * @since 2.0.0
     */
    protected $customerAccountManagement;

    /**
     * @var CustomerGroupManagement
     * @since 2.0.0
     */
    protected $customerGroupManagement;

    /**
     * @var CustomerGroupRepository
     * @since 2.0.0
     */
    protected $customerGroupRepository;

    /**
     * @var CustomerRepository
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * Filter Builder
     *
     * @var FilterBuilder
     * @since 2.0.0
     */
    protected $filterBuilder;

    /**
     * Search Criteria Builder
     *
     * @var SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * Tax Class Repository
     *
     * @var TaxClassRepositoryInterface
     * @since 2.0.0
     */
    protected $taxClassRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $taxConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory
     * @param \Magento\Tax\Model\ResourceModel\Calculation $resource
     * @param CustomerAccountManagement $customerAccountManagement
     * @param CustomerGroupManagement $customerGroupManagement
     * @param CustomerGroupRepository $customerGroupRepository
     * @param CustomerRepository $customerRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Config $taxConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $classesFactory,
        \Magento\Tax\Model\ResourceModel\Calculation $resource,
        CustomerAccountManagement $customerAccountManagement,
        CustomerGroupManagement $customerGroupManagement,
        CustomerGroupRepository $customerGroupRepository,
        CustomerRepository $customerRepository,
        PriceCurrencyInterface $priceCurrency,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_config = $taxConfig;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_classesFactory = $classesFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerRepository = $customerRepository;
        $this->priceCurrency = $priceCurrency;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->taxClassRepository = $taxClassRepository;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Tax\Model\ResourceModel\Calculation::class);
    }

    /**
     * Fetch default customer tax class
     *
     * @param null|Store|string|int $store
     * @return int
     * @since 2.0.0
     */
    public function getDefaultCustomerTaxClass($store = null)
    {
        if ($this->_defaultCustomerTaxClass === null) {
            //Not catching the exception here since default group is expected
            $defaultCustomerGroup = $this->customerGroupManagement->getDefaultGroup($store);
            $this->_defaultCustomerTaxClass = $defaultCustomerGroup->getTaxClassId();
        }
        return $this->_defaultCustomerTaxClass;
    }

    /**
     * Delete calculation settings by rule id
     *
     * @param   int $ruleId
     * @return  $this
     * @since 2.0.0
     */
    public function deleteByRuleId($ruleId)
    {
        $this->_getResource()->deleteByRuleId($ruleId);
        return $this;
    }

    /**
     * Get calculation rates by rule id
     *
     * @param   int $ruleId
     * @return  array
     * @since 2.0.0
     */
    public function getRates($ruleId)
    {
        if (!isset($this->_rates[$ruleId])) {
            $this->_rates[$ruleId] = $this->_getResource()->getCalculationsById('tax_calculation_rate_id', $ruleId);
        }
        return $this->_rates[$ruleId];
    }

    /**
     * Get allowed customer tax classes by rule id
     *
     * @param   int $ruleId
     * @return  array
     * @since 2.0.0
     */
    public function getCustomerTaxClasses($ruleId)
    {
        if (!isset($this->_ctc[$ruleId])) {
            $this->_ctc[$ruleId] = $this->_getResource()->getCalculationsById('customer_tax_class_id', $ruleId);
        }
        return $this->_ctc[$ruleId];
    }

    /**
     * Get allowed product tax classes by rule id
     *
     * @param   int $ruleId
     * @return  array
     * @since 2.0.0
     */
    public function getProductTaxClasses($ruleId)
    {
        if (!isset($this->_ptc[$ruleId])) {
            $this->_ptc[$ruleId] = $this->getResource()->getCalculationsById('product_tax_class_id', $ruleId);
        }
        return $this->_ptc[$ruleId];
    }

    /**
     * Aggregate tax calculation data to array
     *
     * @return array
     * @since 2.0.0
     */
    protected function _formCalculationProcess()
    {
        $title = $this->getRateTitle();
        $value = $this->getRateValue();
        $id = $this->getRateId();

        $rate = ['code' => $title, 'title' => $title, 'percent' => $value, 'position' => 1, 'priority' => 1];

        $process = [];
        $process['percent'] = $value;
        $process['id'] = "{$id}-{$value}";
        $process['rates'][] = $rate;

        return [$process];
    }

    /**
     * Get calculation tax rate by specific request
     *
     * @param   \Magento\Framework\DataObject $request
     * @return  float
     * @since 2.0.0
     */
    public function getRate($request)
    {
        if (!$request->getCountryId() || !$request->getCustomerClassId() || !$request->getProductClassId()) {
            return 0;
        }

        $cacheKey = $this->_getRequestCacheKey($request);
        if (!isset($this->_rateCache[$cacheKey])) {
            $this->unsRateValue();
            $this->unsCalculationProcess();
            $this->unsEventModuleId();
            $this->_eventManager->dispatch('tax_rate_data_fetch', ['request' => $request, 'sender' => $this]);
            if (!$this->hasRateValue()) {
                $rateInfo = $this->_getResource()->getRateInfo($request);
                $this->setCalculationProcess($rateInfo['process']);
                $this->setRateValue($rateInfo['value']);
            } else {
                $this->setCalculationProcess($this->_formCalculationProcess());
            }
            $this->_rateCache[$cacheKey] = $this->getRateValue();
            $this->_rateCalculationProcess[$cacheKey] = $this->getCalculationProcess();
        }
        return $this->_rateCache[$cacheKey];
    }

    /**
     * Get cache key value for specific tax rate request
     *
     * @param   \Magento\Framework\DataObject $request
     * @return  string
     * @since 2.0.0
     */
    protected function _getRequestCacheKey($request)
    {
        $store = $request->getStore();
        $key = '';
        if ($store instanceof \Magento\Store\Model\Store) {
            $key = $store->getId() . '|';
        } elseif (is_numeric($store)) {
            $key = $store . '|';
        }
        $key .= $request->getProductClassId() . '|'
            . $request->getCustomerClassId() . '|'
            . $request->getCountryId() . '|'
            . $request->getRegionId() . '|'
            . $request->getPostcode();
        return $key;
    }

    /**
     * Get tax rate based on store shipping origin address settings
     * This rate can be used for conversion store price including tax to
     * store price excluding tax
     *
     * @param \Magento\Framework\DataObject $request
     * @param null|string|bool|int|Store $store
     * @return float
     * @since 2.0.0
     */
    public function getStoreRate($request, $store = null)
    {
        $storeRequest = $this->getRateOriginRequest($store)->setProductClassId($request->getProductClassId());
        return $this->getRate($storeRequest);
    }

    /**
     * Get request object for getting tax rate based on store shipping original address
     *
     * @param   null|string|bool|int|Store $store
     * @return  \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected function getRateOriginRequest($store = null)
    {
        $request = new \Magento\Framework\DataObject();
        $request->setCountryId(
            $this->_scopeConfig->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
        )->setRegionId(
            $this->_scopeConfig->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
        )->setPostcode(
            $this->_scopeConfig->getValue(
                \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
        )->setCustomerClassId(
            $this->getDefaultCustomerTaxClass($store)
        )->setStore(
            $store
        );
        return $request;
    }

    /**
     * Return the default rate request. It can be either based on store address or customer address
     *
     * @param null|int|string|Store $store
     * @param int $customerId
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getDefaultRateRequest($store = null, $customerId = null)
    {
        if ($this->_isCrossBorderTradeEnabled($store)) {
            //If cross border trade is enabled, we will use customer tax rate as store tax rate
            return $this->getRateRequest(null, null, null, $store, $customerId);
        } else {
            return $this->getRateOriginRequest($store);
        }
    }

    /**
     * Return whether cross border trade is enabled or not
     *
     * @param   null|int|string|Store $store
     * @return  bool
     * @since 2.0.0
     */
    protected function _isCrossBorderTradeEnabled($store = null)
    {
        return (bool)$this->_config->crossBorderTradeEnabled($store);
    }

    /**
     * Get request object with information necessary for getting tax rate
     *
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @param null|bool|\Magento\Framework\DataObject|CustomerAddress $shippingAddress
     * @param null|bool|\Magento\Framework\DataObject|CustomerAddress $billingAddress
     * @param null|int $customerTaxClass
     * @param null|int|\Magento\Store\Model\Store $store
     * @param int $customerId
     * @return  \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function getRateRequest(
        $shippingAddress = null,
        $billingAddress = null,
        $customerTaxClass = null,
        $store = null,
        $customerId = null
    ) {
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address = new \Magento\Framework\DataObject();
        $basedOn = $this->_scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($shippingAddress === false && $basedOn == 'shipping' || $billingAddress === false && $basedOn == 'billing'
        ) {
            $basedOn = 'default';
        } else {
            if (($billingAddress === null || !$billingAddress->getCountryId())
                && $basedOn == 'billing'
                || ($shippingAddress === null || !$shippingAddress->getCountryId())
                && $basedOn == 'shipping'
            ) {
                if ($customerId) {
                    //fallback to default address for registered customer
                    try {
                        $defaultBilling = $this->customerAccountManagement->getDefaultBillingAddress($customerId);
                    } catch (NoSuchEntityException $e) {
                    }

                    try {
                        $defaultShipping = $this->customerAccountManagement->getDefaultShippingAddress($customerId);
                    } catch (NoSuchEntityException $e) {
                    }

                    if ($basedOn == 'billing' && isset($defaultBilling) && $defaultBilling->getCountryId()) {
                        $billingAddress = $defaultBilling;
                    } elseif ($basedOn == 'shipping' && isset($defaultShipping) && $defaultShipping->getCountryId()) {
                        $shippingAddress = $defaultShipping;
                    } else {
                        $basedOn = 'default';
                    }
                } else {
                    //fallback for guest
                    if ($basedOn == 'billing' && is_object($shippingAddress) && $shippingAddress->getCountryId()) {
                        $billingAddress = $shippingAddress;
                    } elseif ($basedOn == 'shipping' && is_object($billingAddress) && $billingAddress->getCountryId()) {
                        $shippingAddress = $billingAddress;
                    } else {
                        $basedOn = 'default';
                    }
                }
            }
        }

        switch ($basedOn) {
            case 'billing':
                $address = $billingAddress;
                break;
            case 'shipping':
                $address = $shippingAddress;
                break;
            case 'origin':
                $address = $this->getRateOriginRequest($store);
                break;
            case 'default':
                $address->setCountryId(
                    $this->_scopeConfig->getValue(
                        \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    )
                )->setRegionId(
                    $this->_scopeConfig->getValue(
                        \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    )
                )->setPostcode(
                    $this->_scopeConfig->getValue(
                        \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    )
                );
                break;
            default:
                break;
        }

        if ($customerTaxClass === null || $customerTaxClass === false) {
            if ($customerId) {
                $customerData = $this->customerRepository->getById($customerId);
                $customerTaxClass = $this->customerGroupRepository
                    ->getById($customerData->getGroupId())
                    ->getTaxClassId();
            } else {
                $customerTaxClass = $this->customerGroupManagement->getNotLoggedInGroup()->getTaxClassId();
            }
        }

        $request = new \Magento\Framework\DataObject();
        //TODO: Address is not completely refactored to use Data objects
        if ($address->getRegion() instanceof AddressRegion) {
            $regionId = $address->getRegion()->getRegionId();
        } else {
            $regionId = $address->getRegionId();
        }
        $request->setCountryId($address->getCountryId())
            ->setRegionId($regionId)
            ->setPostcode($address->getPostcode())
            ->setStore($store)
            ->setCustomerClassId($customerTaxClass);
        return $request;
    }

    /**
     * Get information about tax rates applied to request
     *
     * @param   \Magento\Framework\DataObject $request
     * @return  array
     * @since 2.0.0
     */
    public function getAppliedRates($request)
    {
        if (!$request->getCountryId() || !$request->getCustomerClassId() || !$request->getProductClassId()) {
            return [];
        }

        $cacheKey = $this->_getRequestCacheKey($request);
        if (!isset($this->_rateCalculationProcess[$cacheKey])) {
            $this->_rateCalculationProcess[$cacheKey] = $this->_getResource()->getCalculationProcess($request);
        }
        return $this->_rateCalculationProcess[$cacheKey];
    }

    /**
     * Gets the calculation process
     *
     * @param array $rates
     * @return array
     * @since 2.0.0
     */
    public function reproduceProcess($rates)
    {
        return $this->getResource()->getCalculationProcess(null, $rates);
    }

    /**
     * Calculate rated tax amount based on price and tax rate.
     * If you are using price including tax $priceIncludeTax should be true.
     *
     * @param   float $price
     * @param   float $taxRate
     * @param   boolean $priceIncludeTax
     * @param   boolean $round
     * @return  float
     * @since 2.0.0
     */
    public function calcTaxAmount($price, $taxRate, $priceIncludeTax = false, $round = true)
    {
        $taxRate = $taxRate / 100;

        if ($priceIncludeTax) {
            $amount = $price * (1 - 1 / (1 + $taxRate));
        } else {
            $amount = $price * $taxRate;
        }

        if ($round) {
            return $this->round($amount);
        }

        return $amount;
    }

    /**
     * Round tax amount
     *
     * @param   float $price
     * @return  float
     * @since 2.0.0
     */
    public function round($price)
    {
        return $this->priceCurrency->round($price);
    }

    /**
     * @param array $billingAddress
     * @param array $shippingAddress
     * @param int $customerTaxClassId
     * @return array
     * @since 2.0.0
     */
    public function getTaxRates($billingAddress, $shippingAddress, $customerTaxClassId)
    {
        $billingAddressObj = null;
        $shippingAddressObj = null;
        if (!empty($billingAddress)) {
            $billingAddressObj = new \Magento\Framework\DataObject($billingAddress);
        }
        if (!empty($shippingAddress)) {
            $shippingAddressObj = new \Magento\Framework\DataObject($shippingAddress);
        }
        $rateRequest = $this->getRateRequest($shippingAddressObj, $billingAddressObj, $customerTaxClassId);

        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [$this->filterBuilder->setField(ClassModel::KEY_TYPE)
                 ->setValue(\Magento\Tax\Api\TaxClassManagementInterface::TYPE_PRODUCT)
                 ->create()]
        )->create();
        $ids = $this->taxClassRepository->getList($searchCriteria)->getItems();

        $productRates = [];
        $idKeys = array_keys($ids);
        foreach ($idKeys as $idKey) {
            $rateRequest->setProductClassId($idKey);
            $rate = $this->getRate($rateRequest);
            $productRates[$idKey] = $rate;
        }
        return $productRates;
    }
}
