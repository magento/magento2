<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Shipment\Request;

/**
 * Class AbstractCarrier
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractCarrier extends \Magento\Framework\DataObject implements AbstractCarrierInterface
{
    public const DEBUG_KEYS_MASK = '****';

    /**
     * @var string
     */
    protected $_code;

    /**
     * @var array|null
     */
    protected $_rates;

    /**
     * @var int
     */
    protected $_numBoxes = 1;

    /**
     * @var string
     */
    protected $_freeMethod = 'free_method';

    /**
     * @var bool
     */
    protected $_isFixed = false;

    /**
     * @var string[]
     */
    protected $_customizableContainerTypes = [];

    public const USA_COUNTRY_ID = 'US';

    public const CANADA_COUNTRY_ID = 'CA';

    public const MEXICO_COUNTRY_ID = 'MX';

    public const HANDLING_TYPE_PERCENT = 'P';

    public const HANDLING_TYPE_FIXED = 'F';

    public const HANDLING_ACTION_PERPACKAGE = 'P';

    public const HANDLING_ACTION_PERORDER = 'O';

    /**
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
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory
     */
    protected $_rateErrorFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
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
     * @return  false|string
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * Do request to shipment.
     *
     * Implementation must be in overridden method
     *
     * @param Request $request
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function requestToShipment($request)
    {
        return new \Magento\Framework\DataObject();
    }

    /**
     * Do return of shipment.
     *
     * Implementation must be in overridden method
     *
     * @param Request $request
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function returnOfShipment($request)
    {
        return new \Magento\Framework\DataObject();
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        return [];
    }

    /**
     * Get allowed containers of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getAllowedContainers(\Magento\Framework\DataObject $params = null)
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
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\DataObject $params = null)
    {
        return [];
    }

    /**
     * Validate request for available ship countries.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|false|\Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkAvailableShipCountries(\Magento\Framework\DataObject $request)
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
                 * The admin set not to show the shipping module if the delivery country
                 * is not within specific countries
                 */
                return false;
            }
        }

        return $this;
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 100.2.6
     */
    public function processAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return $this;
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @deprecated 100.2.6
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return $this->processAdditionalValidation($request);
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
     * Check if the request has free shipping weight
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return bool
     */
    private function hasFreeMethodWeight($request): bool
    {
        return (
            $request->getFreeShipping()
            || (
                $request->hasFreeMethodWeight()
                && ((float) $request->getFreeMethodWeight()) !== ((float) $request->getPackageWeight())
            )
        );
    }

    /**
     * Allows free shipping when all product items have free shipping.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _updateFreeMethodQuote($request)
    {
        if (!$this->hasFreeMethodWeight($request)) {
            return;
        }

        $freeMethod = $this->getConfigData($this->_freeMethod);
        if (!$freeMethod) {
            return;
        }
        $freeRateId = false;
        // phpstan:ignore
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
            // phpstan:ignore
            $this->_setFreeMethodRequest($freeMethod);

            // phpstan:ignore
            $result = $this->_getQuotes();
            if ($result && ($rates = $result->getAllRates()) && count($rates) > 0) {
                if (count($rates) == 1 && $rates[0] instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
                    $price = $rates[0]->getPrice();
                }
                if (count($rates) > 1) {
                    foreach ($rates as $rate) {
                        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method &&
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
        if ($price !== null) {
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
        $handlingFee = (float)$this->getConfigData('handling_fee');
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
     * Gets the average weight of each box available for shipping
     *
     * @param float $weight in some measure
     * @return float
     */
    public function getTotalNumOfBoxes($weight)
    {
        /*
        reset num box first before retrieve again
        */
        $this->_numBoxes = 1;
        $maxPackageWeight = $this->getConfigData('max_package_weight');
        if ($weight > $maxPackageWeight && $maxPackageWeight != 0) {
            $this->_numBoxes = ceil($weight / $maxPackageWeight);
            $weight = (float)$weight / $this->_numBoxes;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            $this->_logger->debug(var_export($debugData, true));
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
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
     * @param \Magento\Framework\DataObject $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContentTypes(\Magento\Framework\DataObject $params)
    {
        return [];
    }

    /**
     * Recursive replace sensitive fields of XML document.
     *
     * For example if xml document has the following structure:
     * ```xml
     * <Request>
     *     <LicenseNumber>E437FJFD</LicenseNumber>
     *     <UserId>testUser1</UserId>
     *     <Password>userPassword</Password>
     * </Request>
     * ```
     * and sensitive fields are specified as `['UserId', 'Password']`, then sensitive fields
     * will be replaced by the mask(by default it is '****')
     *
     * @param string $data
     * @return string
     * @since 100.1.0
     */
    protected function filterDebugData($data)
    {
        try {
            $xml = new \SimpleXMLElement($data);
            $this->filterXmlData($xml);
            $data = $xml->asXML();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $data;
    }

    /**
     * Recursive replace sensitive xml nodes values by specified mask.
     *
     * @param \SimpleXMLElement $xml
     * @return void
     */
    private function filterXmlData(\SimpleXMLElement $xml)
    {
        /** @var \SimpleXMLElement $child */
        foreach ($xml->children() as $child) {
            if ($child->count()) {
                $this->filterXmlData($child);
            } elseif (in_array((string) $child->getName(), $this->_debugReplacePrivateDataKeys)) {
                $child[0] = self::DEBUG_KEYS_MASK;
            }
        }
    }
}
