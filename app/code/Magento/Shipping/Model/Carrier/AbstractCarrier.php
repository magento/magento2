<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Carrier;

use Magento\Sales\Model\Quote\Address\AbstractCarrierInterface;
use Magento\Sales\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Shipment\Request;

abstract class AbstractCarrier extends \Magento\Framework\Object implements AbstractCarrierInterface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code;

    /**
     * Rates result
     *
     * @var array|null
     */
    protected $_rates;

    /**
     * Number of boxes in package
     *
     * @var int
     */
    protected $_numBoxes = 1;

    /**
     * Free Method config path
     *
     * @var string
     */
    protected $_freeMethod = 'free_method';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = false;

    /**
     * Container types that could be customized
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = [];

    const USA_COUNTRY_ID = 'US';

    const CANADA_COUNTRY_ID = 'CA';

    const MEXICO_COUNTRY_ID = 'MX';

    const HANDLING_TYPE_PERCENT = 'P';

    const HANDLING_TYPE_FIXED = 'F';

    const HANDLING_ACTION_PERPACKAGE = 'P';

    const HANDLING_ACTION_PERORDER = 'O';

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = [];

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Sales\Model\Quote\Address\RateResult\ErrorFactory
     */
    protected $_rateErrorFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_scopeConfig = $scopeConfig;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->_logger = $logger;
    }

    /**
     * Retrieve information from carrier configuration
     *
     * @param   string $field
     * @return  void|false|string
     */
    public function getConfigData($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'carriers/' . $this->_code . '/' . $field;
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );
    }

    /**
     * Retrieve config flag for store by field
     *
     * @param string $field
     * @return bool
     */
    public function getConfigFlag($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'carriers/' . $this->_code . '/' . $field;
        return $this->_scopeConfig->isSetFlag(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );
    }

    /**
     * Collect and get rates
     *
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool|null
     * @abstract
     */
    abstract public function collectRates(\Magento\Sales\Model\Quote\Address\RateRequest $request);

    /**
     * Do request to shipment
     * Implementation must be in overridden method
     *
     * @param Request $request
     * @return \Magento\Framework\Object
     */
    public function requestToShipment($request)
    {
        return new \Magento\Framework\Object();
    }

    /**
     * Do return of shipment
     * Implementation must be in overridden method
     *
     * @param Request $request
     * @return \Magento\Framework\Object
     */
    public function returnOfShipment($request)
    {
        return new \Magento\Framework\Object();
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\Object|null $params
     * @return array
     */
    public function getContainerTypes(\Magento\Framework\Object $params = null)
    {
        return [];
    }

    /**
     * Get allowed containers of carrier
     *
     * @param \Magento\Framework\Object|null $params
     * @return array|bool
     */
    protected function _getAllowedContainers(\Magento\Framework\Object $params = null)
    {
        $containersAll = $this->getContainerTypesAll();
        if (empty($containersAll)) {
            return [];
        }
        if (empty($params)) {
            return $containersAll;
        }
        $containersFilter = $this->getContainerTypesFilter();
        $containersFiltered = [];
        $method = $params->getMethod();
        $countryShipper = $params->getCountryShipper();
        $countryRecipient = $params->getCountryRecipient();

        if (empty($containersFilter)) {
            return $containersAll;
        }
        if (!$params || !$method || !$countryShipper || !$countryRecipient) {
            return $containersAll;
        }

        if ($countryShipper == self::USA_COUNTRY_ID && $countryRecipient == self::USA_COUNTRY_ID) {
            $direction = 'within_us';
        } else {
            if ($countryShipper == self::USA_COUNTRY_ID && $countryRecipient != self::USA_COUNTRY_ID) {
                $direction = 'from_us';
            } else {
                return $containersAll;
            }
        }

        foreach ($containersFilter as $dataItem) {
            $containers = $dataItem['containers'];
            $filters = $dataItem['filters'];
            if (!empty($filters[$direction]['method']) && in_array($method, $filters[$direction]['method'])) {
                foreach ($containers as $container) {
                    if (!empty($containersAll[$container])) {
                        $containersFiltered[$container] = $containersAll[$container];
                    }
                }
            }
        }

        return !empty($containersFiltered) ? $containersFiltered : $containersAll;
    }

    /**
     * Get Container Types, that could be customized
     *
     * @return string[]
     */
    public function getCustomizableContainerTypes()
    {
        return $this->_customizableContainerTypes;
    }

    /**
     * Return delivery confirmation types of carrier
     *
     * @param \Magento\Framework\Object|null $params
     * @return array
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\Object $params = null)
    {
        return [];
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @return $this|bool|false|\Magento\Framework\Model\AbstractModel
     */
    public function checkAvailableShipCountries(\Magento\Sales\Model\Quote\Address\RateRequest $request)
    {
        $speCountriesAllow = $this->getConfigData('sallowspecific');
        /*
         * for specific countries, the flag will be 1
         */
        if ($speCountriesAllow && $speCountriesAllow == 1) {
            $showMethod = $this->getConfigData('showmethod');
            $availableCountries = [];
            if ($this->getConfigData('specificcountry')) {
                $availableCountries = explode(',', $this->getConfigData('specificcountry'));
            }
            if ($availableCountries && in_array($request->getDestCountryId(), $availableCountries)) {
                return $this;
            } elseif ($showMethod && (!$availableCountries || $availableCountries && !in_array(
                $request->getDestCountryId(),
                $availableCountries
            ))
            ) {
                /** @var Error $error */
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $errorMsg = $this->getConfigData('specificerrmsg');
                $error->setErrorMessage(
                    $errorMsg ? $errorMsg : __(
                        'Sorry, but we can\'t deliver to the destination country with this shipping module.'
                    )
                );
                return $error;
            } else {
                /*
                 * The admin set not to show the shipping module if the delivery country is not within specific countries
                 */
                return false;
            }
        }
        return $this;
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @return $this|bool|Error
     */
    public function proccessAdditionalValidation(\Magento\Sales\Model\Quote\Address\RateRequest $request)
    {
        return $this;
    }

    /**
     * Determine whether current carrier enabled for activity
     *
     * @return bool
     */
    public function isActive()
    {
        $active = $this->getConfigData('active');
        return $active == 1 || $active == 'true';
    }

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @return bool
     */
    public function isFixed()
    {
        return $this->_isFixed;
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return false;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return bool
     */
    public function isShippingLabelsAvailable()
    {
        return false;
    }

    /**
     *  Retrieve sort order of current carrier
     *
     * @return string|null
     */
    public function getSortOrder()
    {
        return $this->getConfigData('sort_order');
    }

    /**
     * @param \Magento\Sales\Model\Quote\Address\RateRequest $request
     * @return void
     */
    protected function _updateFreeMethodQuote($request)
    {
        if ($request->getFreeMethodWeight() == $request->getPackageWeight() || !$request->hasFreeMethodWeight()) {
            return;
        }

        $freeMethod = $this->getConfigData($this->_freeMethod);
        if (!$freeMethod) {
            return;
        }
        $freeRateId = false;

        if (is_object($this->_result)) {
            foreach ($this->_result->getAllRates() as $i => $item) {
                if ($item->getMethod() == $freeMethod) {
                    $freeRateId = $i;
                    break;
                }
            }
        }

        if ($freeRateId === false) {
            return;
        }
        $price = null;
        if ($request->getFreeMethodWeight() > 0) {
            $this->_setFreeMethodRequest($freeMethod);

            $result = $this->_getQuotes();
            if ($result && ($rates = $result->getAllRates()) && count($rates) > 0) {
                if (count($rates) == 1 && $rates[0] instanceof \Magento\Sales\Model\Quote\Address\RateResult\Method) {
                    $price = $rates[0]->getPrice();
                }
                if (count($rates) > 1) {
                    foreach ($rates as $rate) {
                        if ($rate instanceof \Magento\Sales\Model\Quote\Address\RateResult\Method &&
                            $rate->getMethod() == $freeMethod
                        ) {
                            $price = $rate->getPrice();
                        }
                    }
                }
            }
        } else {
            /**
             * if we can apply free shipping for all order we should force price
             * to $0.00 for shipping with out sending second request to carrier
             */
            $price = 0;
        }

        /**
         * if we did not get our free shipping method in response we must use its old price
         */
        if (!is_null($price)) {
            $this->_result->getRateById($freeRateId)->setPrice($price);
        }
    }

    /**
     * Get the handling fee for the shipping + cost
     *
     * @param float $cost
     * @return float final price for shipping method
     */
    public function getFinalPriceWithHandlingFee($cost)
    {
        $handlingFee = $this->getConfigData('handling_fee');
        $handlingType = $this->getConfigData('handling_type');
        if (!$handlingType) {
            $handlingType = self::HANDLING_TYPE_FIXED;
        }
        $handlingAction = $this->getConfigData('handling_action');
        if (!$handlingAction) {
            $handlingAction = self::HANDLING_ACTION_PERORDER;
        }

        return $handlingAction == self::HANDLING_ACTION_PERPACKAGE ? $this->_getPerpackagePrice(
            $cost,
            $handlingType,
            $handlingFee
        ) : $this->_getPerorderPrice(
            $cost,
            $handlingType,
            $handlingFee
        );
    }

    /**
     * Get final price for shipping method with handling fee per package
     *
     * @param float $cost
     * @param string $handlingType
     * @param float $handlingFee
     * @return float
     */
    protected function _getPerpackagePrice($cost, $handlingType, $handlingFee)
    {
        if ($handlingType == self::HANDLING_TYPE_PERCENT) {
            return ($cost + $cost * $handlingFee / 100) * $this->_numBoxes;
        }

        return ($cost + $handlingFee) * $this->_numBoxes;
    }

    /**
     * Get final price for shipping method with handling fee per order
     *
     * @param float $cost
     * @param string $handlingType
     * @param float $handlingFee
     * @return float
     */
    protected function _getPerorderPrice($cost, $handlingType, $handlingFee)
    {
        if ($handlingType == self::HANDLING_TYPE_PERCENT) {
            return $cost * $this->_numBoxes + $cost * $this->_numBoxes * $handlingFee / 100;
        }

        return $cost * $this->_numBoxes + $handlingFee;
    }

    /**
     * Return weight in pounds
     *
     * @param int $weight In some measure
     * @return float Weight in pounds
     */
    public function convertWeightToLbs($weight)
    {
        return $weight;
    }

    /**
     * Sets the number of boxes for shipping
     *
     * @param int $weight in some measure
     * @return int
     */
    public function getTotalNumOfBoxes($weight)
    {
        /*
        reset num box first before retrieve again
        */
        $this->_numBoxes = 1;
        $weight = $this->convertWeightToLbs($weight);
        $maxPackageWeight = $this->getConfigData('max_package_weight');
        if ($weight > $maxPackageWeight && $maxPackageWeight != 0) {
            $this->_numBoxes = ceil($weight / $maxPackageWeight);
            $weight = $weight / $this->_numBoxes;
        }
        return $weight;
    }

    /**
     * Is state province required
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        return false;
    }

    /**
     * Check if city option required
     *
     * @return bool
     */
    public function isCityRequired()
    {
        return false;
    }

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        return false;
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     * @return void
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            $this->_logger->debug($debugData);
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->getConfigData('debug');
    }

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     * @return void
     */
    public function debugData($debugData)
    {
        $this->_debug($debugData);
    }

    /**
     * Getter for carrier code
     *
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->_code;
    }

    /**
     * Return content types of package
     *
     * @param \Magento\Framework\Object $params
     * @return array
     */
    public function getContentTypes(\Magento\Framework\Object $params)
    {
        return [];
    }
}
