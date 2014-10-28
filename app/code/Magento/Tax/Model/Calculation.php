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
namespace Magento\Tax\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Customer\Service\V1\Data\Customer as CustomerDataObject;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Customer\Service\V1\Data\Region as RegionDataObject;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface as AddressServiceInterface;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface as GroupServiceInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\Config;

/**
 * Tax Calculation Model
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
     */
    protected $_rates = array();

    /**
     * Identifier constant for row based calculation
     *
     * @var array
     */
    protected $_ctc = array();

    /**
     * Identifier constant for total based calculation
     *
     * @var array
     */
    protected $_ptc = array();

    /**
     * Cache to hold the rates
     *
     * @var array
     */
    protected $_rateCache = array();

    /**
     * Store the rate calculation process
     *
     * @var array
     */
    protected $_rateCalculationProcess = array();

    /**
     * Hold the customer
     *
     * @var CustomerDataObject|bool
     */
    protected $_customer;

    /**
     * @var int
     */
    protected $_defaultCustomerTaxClass;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Tax\Model\Resource\TaxClass\CollectionFactory
     */
    protected $_classesFactory;

    /**
     * Tax configuration object
     *
     * @var Config
     */
    protected $_config;

    /**
     * @var GroupServiceInterface
     */
    protected $_groupService;

    /**
     * @var CustomerAccountServiceInterface
     */
    protected $customerAccountService;

    /**
     * @var CustomerBuilder
     */
    protected $customerBuilder;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $taxConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Resource\TaxClass\CollectionFactory $classesFactory
     * @param Resource\Calculation $resource
     * @param AddressServiceInterface $addressService
     * @param GroupServiceInterface $groupService
     * @param CustomerAccountServiceInterface $customerAccount
     * @param CustomerBuilder $customerBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Config $taxConfig,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Tax\Model\Resource\TaxClass\CollectionFactory $classesFactory,
        \Magento\Tax\Model\Resource\Calculation $resource,
        AddressServiceInterface $addressService,
        GroupServiceInterface $groupService,
        CustomerAccountServiceInterface $customerAccount,
        CustomerBuilder $customerBuilder,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_config = $taxConfig;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_classesFactory = $classesFactory;
        $this->_addressService = $addressService;
        $this->_groupService = $groupService;
        $this->customerAccountService = $customerAccount;
        $this->customerBuilder = $customerBuilder;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\Resource\Calculation');
    }

    /**
     * Fetch default customer tax class
     *
     * @param null|Store|string|int $store
     * @return int
     */
    public function getDefaultCustomerTaxClass($store = null)
    {
        if ($this->_defaultCustomerTaxClass === null) {
            //Not catching the exception here since default group is expected
            $defaultCustomerGroup = $this->_groupService->getDefaultGroup($store);
            $this->_defaultCustomerTaxClass = $defaultCustomerGroup->getTaxClassId();
        }
        return $this->_defaultCustomerTaxClass;
    }

    /**
     * Delete calculation settings by rule id
     *
     * @param   int $ruleId
     * @return  $this
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
     */
    protected function _formCalculationProcess()
    {
        $title = $this->getRateTitle();
        $value = $this->getRateValue();
        $id = $this->getRateId();

        $rate = array('code' => $title, 'title' => $title, 'percent' => $value, 'position' => 1, 'priority' => 1);

        $process = array();
        $process['percent'] = $value;
        $process['id'] = "{$id}-{$value}";
        $process['rates'][] = $rate;

        return array($process);
    }

    /**
     * Get calculation tax rate by specific request
     *
     * @param   \Magento\Framework\Object $request
     * @return  float
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
            $this->_eventManager->dispatch('tax_rate_data_fetch', array('request' => $request, 'sender' => $this));
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
     * @param   \Magento\Framework\Object $request
     * @return  string
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
     * @param \Magento\Framework\Object $request
     * @param null|string|bool|int|Store $store
     * @return float
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
     * @return  \Magento\Framework\Object
     */
    public function getRateOriginRequest($store = null)
    {
        $request = new \Magento\Framework\Object();
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
     * @return \Magento\Framework\Object
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
     * @param null|bool|\Magento\Framework\Object|\Magento\Customer\Service\V1\Data\Address $shippingAddress
     * @param null|bool|\Magento\Framework\Object|\Magento\Customer\Service\V1\Data\Address $billingAddress
     * @param null|int $customerTaxClass
     * @param null|int|\Magento\Store\Model\Store $store
     * @param int $customerId
     * @return  \Magento\Framework\Object
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
        $address = new \Magento\Framework\Object();
        $basedOn = $this->_scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($shippingAddress === false && $basedOn == 'shipping' || $billingAddress === false && $basedOn == 'billing'
        ) {
            $basedOn = 'default';
        } else {

            if ((is_null($billingAddress) || !$billingAddress->getCountryId())
                && $basedOn == 'billing'
                || (is_null($shippingAddress) || !$shippingAddress->getCountryId())
                && $basedOn == 'shipping'
            ) {
                if ($customerId) {
                    try {
                        $defaultBilling = $this->_addressService->getDefaultBillingAddress($customerId);
                    } catch (NoSuchEntityException $e) {
                    }

                    try {
                        $defaultShipping = $this->_addressService->getDefaultShippingAddress($customerId);
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
                    $basedOn = 'default';
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

        if (is_null($customerTaxClass) || $customerTaxClass === false) {
            if ($customerId) {
                $customerData = $this->customerAccountService->getCustomer($customerId);
                $customerTaxClass = $this->_groupService->getGroup($customerData->getGroupId())->getTaxClassId();
            } else {
                $customerTaxClass = $this->_groupService->getGroup(
                    GroupServiceInterface::NOT_LOGGED_IN_ID
                )->getTaxClassId();
            }
        }

        $request = new \Magento\Framework\Object();
        //TODO: Address is not completely refactored to use Data objects
        if ($address->getRegion() instanceof RegionDataObject) {
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
     * Compare data and rates for two tax rate requests for same products (product tax class ids).
     * Returns true if requests are similar (i.e. equal taxes rates will be applied to them)
     *
     * Notice:
     * a) productClassId MUST be identical for both requests, because we intend to check selling SAME products to DIFFERENT locations
     * b) due to optimization productClassId can be array of ids, not only single id
     *
     * @param   \Magento\Framework\Object $first
     * @param   \Magento\Framework\Object $second
     * @return  bool
     */
    public function compareRequests($first, $second)
    {
        $country = $first->getCountryId() == $second->getCountryId();
        // "0" support for admin dropdown with --please select--
        $region = (int)$first->getRegionId() == (int)$second->getRegionId();
        $postcode = $first->getPostcode() == $second->getPostcode();
        $taxClass = $first->getCustomerClassId() == $second->getCustomerClassId();

        if ($country && $region && $postcode && $taxClass) {
            return true;
        }
        /**
         * Compare available tax rates for both requests
         */
        $firstReqRates = $this->_getResource()->getRateIds($first);
        $secondReqRates = $this->_getResource()->getRateIds($second);
        if ($firstReqRates === $secondReqRates) {
            return true;
        }

        /**
         * If rates are not equal by ids then compare actual values
         * All product classes must have same rates to assume requests been similar
         */
        $productClassId1 = $first->getProductClassId();
        // Save to set it back later
        $productClassId2 = $second->getProductClassId();
        // Save to set it back later

        // Ids are equal for both requests, so take any of them to process
        $ids = is_array($productClassId1) ? $productClassId1 : array($productClassId1);
        $identical = true;
        foreach ($ids as $productClassId) {
            $first->setProductClassId($productClassId);
            $rate1 = $this->getRate($first);

            $second->setProductClassId($productClassId);
            $rate2 = $this->getRate($second);

            if ($rate1 != $rate2) {
                $identical = false;
                break;
            }
        }

        $first->setProductClassId($productClassId1);
        $second->setProductClassId($productClassId2);

        return $identical;
    }

    /**
     * Get information about tax rates applied to request
     *
     * @param   \Magento\Framework\Object $request
     * @return  array
     */
    public function getAppliedRates($request)
    {
        if (!$request->getCountryId() || !$request->getCustomerClassId() || !$request->getProductClassId()) {
            return array();
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
     */
    public function reproduceProcess($rates)
    {
        return $this->getResource()->getCalculationProcess(null, $rates);
    }

    /**
     * Get rates by customer tax class
     *
     * @param int $customerTaxClass
     * @return array
     */
    public function getRatesByCustomerTaxClass($customerTaxClass)
    {
        return $this->getResource()->getRatesByCustomerTaxClass($customerTaxClass);
    }

    /**
     * Get rates by customer and product classes
     *
     * @param int $customerTaxClass
     * @param int $productTaxClass
     * @return array
     */
    public function getRatesByCustomerAndProductTaxClasses($customerTaxClass, $productTaxClass)
    {
        return $this->getResource()->getRatesByCustomerTaxClass($customerTaxClass, $productTaxClass);
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
     * Truncate number to specified precision
     *
     * @param   float $price
     * @param   int $precision
     * @return  float
     */
    public function truncate($price, $precision = 4)
    {
        $exp = pow(10, $precision);
        $price = floor($price * $exp) / $exp;
        return $price;
    }

    /**
     * Round tax amount
     *
     * @param   float $price
     * @return  float
     */
    public function round($price)
    {
        return $this->priceCurrency->round($price);
    }

    /**
     * Round price up
     *
     * @param   float $price
     * @return  float
     */
    public function roundUp($price)
    {
        return ceil($price * 100) / 100;
    }
}
