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

use Magento\Core\Model\Store;
use Magento\Customer\Service\V1\Data\Customer as CustomerDataObject;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Customer\Service\V1\Data\Region as RegionDataObject;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface as AddressServiceInterface;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface as GroupServiceInterface;
use Magento\Exception\NoSuchEntityException;

/**
 * Tax Calculation Model
 */
class Calculation extends \Magento\Model\AbstractModel
{
    const CALC_TAX_BEFORE_DISCOUNT_ON_EXCL = '0_0';

    const CALC_TAX_BEFORE_DISCOUNT_ON_INCL = '0_1';

    const CALC_TAX_AFTER_DISCOUNT_ON_EXCL = '1_0';

    const CALC_TAX_AFTER_DISCOUNT_ON_INCL = '1_1';

    const CALC_UNIT_BASE = 'UNIT_BASE_CALCULATION';

    const CALC_ROW_BASE = 'ROW_BASE_CALCULATION';

    const CALC_TOTAL_BASE = 'TOTAL_BASE_CALCULATION';

    /**
     * @var array
     */
    protected $_rates = array();

    /**
     * @var array
     */
    protected $_ctc = array();

    /**
     * @var array
     */
    protected $_ptc = array();

    /**
     * @var array
     */
    protected $_rateCache = array();

    /**
     * @var array
     */
    protected $_rateCalculationProcess = array();

    /**
     * @var \Magento\Customer\Model\Customer|bool
     */
    protected $_customer;

    /**
     * @var int
     */
    protected $_defaultCustomerTaxClass;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_groupFactory;

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
     * @var GroupServiceInterface
     */
    protected $_groupService;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    protected $_converter;

    /**
     * @var CustomerBuilder
     */
    protected $_customerBuilder;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Tax\Model\Resource\TaxClass\CollectionFactory $classesFactory
     * @param \Magento\Tax\Model\Resource\Calculation $resource
     * @param AddressServiceInterface $addressService
     * @param GroupServiceInterface $groupService
     * @param \Magento\Customer\Model\Converter $converter
     * @param CustomerBuilder $customerBuilder
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Tax\Model\Resource\TaxClass\CollectionFactory $classesFactory,
        \Magento\Tax\Model\Resource\Calculation $resource,
        AddressServiceInterface $addressService,
        GroupServiceInterface $groupService,
        \Magento\Customer\Model\Converter $converter,
        CustomerBuilder $customerBuilder,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_groupFactory = $groupFactory;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_classesFactory = $classesFactory;
        $this->_addressService = $addressService;
        $this->_groupService = $groupService;
        $this->_converter = $converter;
        $this->_customerBuilder = $customerBuilder;

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
     * Specify customer object which can be used for rate calculation
     *
     * @deprecated in favor of \Magento\Tax\Model\Calculation::setCustomerData
     *
     * @param   \Magento\Customer\Model\Customer $customer
     * @return  $this
     */
    public function setCustomer(\Magento\Customer\Model\Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Specify customer object which can be used for rate calculation
     *
     * @param CustomerDataObject $customerData
     * @return $this
     */
    public function setCustomerData(CustomerDataObject $customerData)
    {
        /* @TODO: remove model usage in favor of Data Object */
        $customer = $this->_converter->createCustomerModel($customerData);
        $this->setCustomer($customer);
        return $this;
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
     * Get customer object
     *
     * @deprecated in favor of \Magento\Tax\Model\Calculation::getCustomerData
     *
     * @return  \Magento\Customer\Model\Customer|bool
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            if ($this->_customerSession->isLoggedIn()) {
                $this->_customer = $this->_customerSession->getCustomer();
            } elseif ($this->_customerSession->getCustomerId()) {
                /** @var $customer \Magento\Customer\Model\Customer */
                $customer = $this->_customerFactory->create();
                $this->_customer = $customer->load($this->_customerSession->getCustomerId());
            } else {
                $this->_customer = false;
            }
        }
        return $this->_customer;
    }

    /**
     * Retrieve customer data object
     *
     * @return CustomerDataObject
     */
    public function getCustomerData()
    {
        /* @TODO: remove this code in favor of setCustomerData*/
        $customerModel = $this->getCustomer();
        //getCustomer can return false. Returning empty data object as a workaround. This  behavior needs to be fixed
        // for now till the time \Magento\Tax\Model\Calculation::getCustomer is removed.
        if (!$customerModel) {
            return $this->_customerBuilder->create();
        }
        return $this->_converter->createCustomerFromModel($customerModel);
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
            $this->_rates[$ruleId] = $this->_getResource()->getDistinct('tax_calculation_rate_id', $ruleId);
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
            $this->_ctc[$ruleId] = $this->_getResource()->getDistinct('customer_tax_class_id', $ruleId);
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
            $this->_ptc[$ruleId] = $this->getResource()->getDistinct('product_tax_class_id', $ruleId);
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
     * @param   \Magento\Object $request
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
     * @param   \Magento\Object $request
     * @return  string
     */
    protected function _getRequestCacheKey($request)
    {
        $key = $request->getStore() ? $request->getStore()->getId() . '|' : '';
        $key .= $request->getProductClassId() .
            '|' .
            $request->getCustomerClassId() .
            '|' .
            $request->getCountryId() .
            '|' .
            $request->getRegionId() .
            '|' .
            $request->getPostcode();
        return $key;
    }

    /**
     * Get tax rate based on store shipping origin address settings
     * This rate can be used for conversion store price including tax to
     * store price excluding tax
     *
     * @param \Magento\Object $request
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
     * @return  \Magento\Object
     */
    public function getRateOriginRequest($store = null)
    {
        $request = new \Magento\Object();
        $request->setCountryId(
            $this->_coreStoreConfig->getConfig(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID, $store)
        )->setRegionId(
            $this->_coreStoreConfig->getConfig(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID, $store)
        )->setPostcode(
            $this->_coreStoreConfig->getConfig(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE, $store)
        )->setCustomerClassId(
            $this->getDefaultCustomerTaxClass($store)
        )->setStore(
            $store
        );
        return $request;
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
     * @param   null|bool|\Magento\Object $shippingAddress
     * @param   null|bool||\Magento\Object $billingAddress
     * @param   null|int $customerTaxClass
     * @param   null|int $store
     * @return  \Magento\Object
     */
    public function getRateRequest(
        $shippingAddress = null,
        $billingAddress = null,
        $customerTaxClass = null,
        $store = null
    ) {
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address = new \Magento\Object();
        $customerData = $this->getCustomerData();
        $basedOn = $this->_coreStoreConfig->getConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON, $store);

        if ($shippingAddress === false && $basedOn == 'shipping' || $billingAddress === false && $basedOn == 'billing'
        ) {
            $basedOn = 'default';
        } else {
            if (($billingAddress === false || is_null(
                $billingAddress
            ) || !$billingAddress->getCountryId()) && $basedOn == 'billing' || ($shippingAddress === false || is_null(
                $shippingAddress
            ) || !$shippingAddress->getCountryId()) && $basedOn == 'shipping'
            ) {
                if ($customerData->getId()) {
                    try {
                        $defaultBilling = $this->_addressService->getDefaultBillingAddress($customerData->getId());
                    } catch (NoSuchEntityException $e) {
                        /** Address does not exist */
                    }

                    try {
                        $defaultShipping = $this->_addressService->getDefaultShippingAddress($customerData->getId());
                    } catch (NoSuchEntityException $e) {
                        /** Address does not exist */
                    }

                    if ($basedOn == 'billing' && $defaultBilling && $defaultBilling->getCountryId()) {
                        $billingAddress = $defaultBilling;
                    } elseif ($basedOn == 'shipping' && $defaultShipping && $defaultShipping->getCountryId()) {
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
                    $this->_coreStoreConfig->getConfig(
                        \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                        $store
                    )
                )->setRegionId(
                    $this->_coreStoreConfig->getConfig(
                        \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
                        $store
                    )
                )->setPostcode(
                    $this->_coreStoreConfig->getConfig(
                        \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                        $store
                    )
                );
                break;
            default:
                break;
        }

        if (is_null($customerTaxClass) && $customerData->getId()) {
            $customerTaxClass = $this->_groupService->getGroup($customerData->getGroupId())->getTaxClassId();
        } elseif ($customerTaxClass === false || !$customerData->getId()) {
            $customerTaxClass = $this->getDefaultCustomerTaxClass($store);
        }

        $request = new \Magento\Object();
        //TODO: Address is not completely refactored to use Data objects
        if ($address->getRegion() instanceof RegionDataObject) {
            $regionId = $address->getRegion()->getRegionId();
        } else {
            $regionId = $address->getRegionId();
        }
        $request->setCountryId(
            $address->getCountryId()
        )->setRegionId(
            $regionId
        )->setPostcode(
            $address->getPostcode()
        )->setStore(
            $store
        )->setCustomerClassId(
            $customerTaxClass
        );
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
     * @param   \Magento\Object $first
     * @param   \Magento\Object $second
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
     * @param \Magento\Object $request
     * @param string|array $fieldName
     * @param string|array $type
     * @return array
     */
    protected function _getRates($request, $fieldName, $type)
    {
        $result = array();
        /** @var $classes \Magento\Tax\Model\Resource\TaxClass\Collection */
        $classes = $this->_classesFactory->create();
        $classes->addFieldToFilter('class_type', $type)->load();
        foreach ($classes as $class) {
            $request->setData($fieldName, $class->getId());
            $result[$class->getId()] = $this->getRate($request);
        }
        return $result;
    }

    /**
     * @param \Magento\Object $request
     * @return array
     */
    public function getRatesForAllProductTaxClasses($request)
    {
        return $this->_getRates($request, 'product_class_id', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT);
    }

    /**
     * @param \Magento\Object $request
     * @return array
     */
    public function getRatesForAllCustomerTaxClasses($request)
    {
        return $this->_getRates($request, 'customer_class_id', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER);
    }

    /**
     * Get information about tax rates applied to request
     *
     * @param   \Magento\Object $request
     * @return  array
     */
    public function getAppliedRates($request)
    {
        $cacheKey = $this->_getRequestCacheKey($request);
        if (!isset($this->_rateCalculationProcess[$cacheKey])) {
            $this->_rateCalculationProcess[$cacheKey] = $this->_getResource()->getCalculationProcess($request);
        }
        return $this->_rateCalculationProcess[$cacheKey];
    }

    /**
     * @param array $rates
     * @return array
     */
    public function reproduceProcess($rates)
    {
        return $this->getResource()->getCalculationProcess(null, $rates);
    }

    /**
     * @param int $customerTaxClass
     * @return array
     */
    public function getRatesByCustomerTaxClass($customerTaxClass)
    {
        return $this->getResource()->getRatesByCustomerTaxClass($customerTaxClass);
    }

    /**
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
        return $this->_storeManager->getStore()->roundPrice($price);
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
