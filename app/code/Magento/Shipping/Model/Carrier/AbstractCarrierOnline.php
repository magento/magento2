<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Model\Carrier;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Framework\Xml\Security;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

use function in_array;

/**
 * Abstract online shipping carrier model
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractCarrierOnline extends AbstractCarrier
{
    /**
     * Samoa American
     * Guam
     * Northern Mariana Islands
     * Palau
     * Puerto Rico
     * Virgin Islands US
     * United States
     */
    public const US_COUNTY_IDS = ['AS', 'GU', 'MP', 'PW', 'PR', 'VI', 'US'];

    public const USA_COUNTRY_ID = 'US';

    public const PUERTORICO_COUNTRY_ID = 'PR';

    public const GUAM_COUNTRY_ID = 'GU';

    public const GUAM_REGION_CODE = 'GU';

    /**
     * @var array
     */
    protected static $_quotesCache = [];

    /**
     * @var string
     *
     * @deprecated
     * @see \Magento\Shipping\Model\Carrier\AbstractCarrier::isActive
     */
    protected $_activeFlag = 'active';

    /**
     * @var Data
     */
    protected $_directoryData = null;

    /**
     * @var ElementFactory
     */
    protected $_xmlElFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var ResultFactory
     */
    protected $_trackFactory;

    /**
     * @var ErrorFactory
     */
    protected $_trackErrorFactory;

    /**
     * @var StatusFactory
     */
    protected $_trackStatusFactory;

    /**
     * @var RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Raw rate request data
     *
     * @var DataObject|null
     */
    protected $_rawRequest = null;

    /**
     * @var Security
     */
    protected $xmlSecurity;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        MethodFactory $rateMethodFactory,
        ResultFactory $trackFactory,
        ErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->_xmlElFactory = $xmlElFactory;
        $this->_rateFactory = $rateFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_trackFactory = $trackFactory;
        $this->_trackErrorFactory = $trackErrorFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        $this->_currencyFactory = $currencyFactory;
        $this->_directoryData = $directoryData;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->xmlSecurity = $xmlSecurity;
    }

    /**
     * Set flag for check carriers for activity
     *
     * @param string $code
     * @return $this
     *
     * @deprecated
     * @see \Magento\Shipping\Model\Carrier\AbstractCarrier::isActive
     */
    public function setActiveFlag($code = 'active')
    {
        $this->_activeFlag = $code;

        return $this;
    }

    /**
     * Return code of carrier
     *
     * @return string|null
     */
    public function getCarrierCode()
    {
        return $this->_code ?? null;
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     * @return string|false
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * All \Magento\Usa carriers have shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Check if city option required
     *
     * @return boolean
     */
    public function isCityRequired()
    {
        return true;
    }

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        if ($countryId != null) {
            return !$this->_directoryData->isZipCodeOptional($countryId);
        }

        return true;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Return items for further shipment rate evaluation. We need to pass children of a bundle instead passing the
     * bundle itself, otherwise we may not get a rate at all (e.g. when total weight of a bundle exceeds max weight
     * despite each item by itself is not)
     *
     * @param RateRequest $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAllItems(RateRequest $request)
    {
        $items = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /* @var $item Item */
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    // Don't process children here - we will process (or already have processed) them below
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $items[] = $child;
                        }
                    }
                } else {
                    // Ship together - count compound item as one solid
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Processing additional validation to check if carrier applicable.
     *
     * @param DataObject $request
     * @return $this|bool|DataObject
     * @deprecated 100.2.6
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function proccessAdditionalValidation(DataObject $request)
    {
        return $this->processAdditionalValidation($request);
    }

    /**
     * Processing additional validation to check if carrier applicable.
     *
     * @param DataObject $request
     * @return $this|bool|DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 100.2.6
     */
    public function processAdditionalValidation(DataObject $request)
    {
        //Skip by item validation if there is no items in request
        if (!count($this->getAllItems($request))) {
            return $this;
        }

        $maxAllowedWeight = (double)$this->getConfigData('max_package_weight');
        $errorMsg = '';
        $configErrorMsg = $this->getConfigData('specificerrmsg');
        $defaultErrorMsg = __('The shipping module is not available.');
        $showMethod = $this->getConfigData('showmethod');

        /** @var $item Item */
        foreach ($this->getAllItems($request) as $item) {
            $product = $item->getProduct();
            if ($product && $product->getId()) {
                $weight = $product->getWeight();
                $stockItemData = $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $item->getStore()->getWebsiteId()
                );
                $doValidation = true;

                if ($stockItemData->getIsQtyDecimal() && $stockItemData->getIsDecimalDivided()) {
                    if ($stockItemData->getEnableQtyIncrements() && $stockItemData->getQtyIncrements()
                    ) {
                        $weight = $weight * $stockItemData->getQtyIncrements();
                    } else {
                        $doValidation = false;
                    }
                } elseif ($stockItemData->getIsQtyDecimal() && !$stockItemData->getIsDecimalDivided()) {
                    $weight = $weight * $item->getQty();
                }

                if ($doValidation && $weight > $maxAllowedWeight) {
                    $errorMsg = $configErrorMsg ? $configErrorMsg : $defaultErrorMsg;
                    break;
                }
            }
        }

        if (!$errorMsg && !$request->getDestPostcode() && $this->isZipCodeRequired($request->getDestCountryId())) {
            $errorMsg = __('This shipping method is not available. Please specify the zip code.');
        }

        if ($errorMsg && $showMethod) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorMsg);

            return $error;
        }
        if ($errorMsg) {
            return false;
        }

        return $this;
    }

    /**
     * Returns cache key for some request to carrier quotes service
     *
     * @param string|array $requestParams
     * @return string
     */
    protected function _getQuotesCacheKey($requestParams)
    {
        if (is_array($requestParams)) {
            $requestParams = implode(
                ',',
                array_merge([$this->getCarrierCode()], array_keys($requestParams), $requestParams)
            );
        }

        return crc32($requestParams);
    }

    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     *
     * Used to reduce number of same requests done to carrier service during one session
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function _getCachedQuotes($requestParams)
    {
        $key = $this->_getQuotesCacheKey($requestParams);

        return self::$_quotesCache[$key] ?? null;
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return $this
     */
    protected function _setCachedQuotes($requestParams, $response)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        self::$_quotesCache[$key] = $response;

        return $this;
    }

    /**
     * Prepare service name. Strip tags and entities from name
     *
     * @param string|object $name service name or object with implemented __toString() method
     * @return string              prepared service name
     */
    protected function _prepareServiceName($name)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $name = html_entity_decode((string)$name);
        $name = strip_tags(preg_replace('#&\w+;#', '', $name));

        return trim($name);
    }

    /**
     * Prepare shipment request. Validate and correct request information
     *
     * @param DataObject $request
     * @return void
     */
    protected function _prepareShipmentRequest(DataObject $request)
    {
        $phonePattern = '/[\s\_\-\(\)]+/';
        $phoneNumber = $request->getShipperContactPhoneNumber();
        $phoneNumber = is_string($phoneNumber) ? preg_replace($phonePattern, '', $phoneNumber) : '';
        $request->setShipperContactPhoneNumber($phoneNumber);
        $phoneNumber = $request->getRecipientContactPhoneNumber();
        $phoneNumber = is_string($phoneNumber) ? preg_replace($phonePattern, '', $phoneNumber) : '';
        $request->setRecipientContactPhoneNumber($phoneNumber);
    }

    /**
     * Do request to shipment
     *
     * @param Request $request
     * @return DataObject
     * @throws LocalizedException
     */
    public function requestToShipment($request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new LocalizedException(__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }
        $data = [];
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new DataObject($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->_doShipmentRequest($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            }

            $data[] = [
                'tracking_number' => $result->getTrackingNumber(),
                'label_content' => $result->getShippingLabelContent(),
            ];
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new DataObject(['info' => $data]);
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }

        return $response;
    }

    /**
     * Do request to RMA shipment
     *
     * @param Request $request
     * @return DataObject
     * @throws LocalizedException
     */
    public function returnOfShipment($request)
    {
        $request->setIsReturn(true);
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new LocalizedException(__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }
        $data = [];
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new DataObject($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->_doShipmentRequest($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $data[] = [
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content' => $result->getShippingLabelContent(),
                ];
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new DataObject(['info' => $data]);
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }

        return $response;
    }

    /**
     * For multi package shipments. Delete requested shipments if the current shipment. Request is failed
     *
     * @param array $data
     * @return bool
     *
     * @todo implement rollback logic
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function rollBack($data)
    {
        return true;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param DataObject $request
     * @return DataObject
     */
    abstract protected function _doShipmentRequest(DataObject $request);

    /**
     * Check is Country U.S. Possessions and Trust Territories
     *
     * @param string $countyId
     * @return boolean
     */
    protected function _isUSCountry($countyId)
    {
        return in_array($countyId, self::US_COUNTY_IDS, true);
    }

    /**
     * Check whether girth is allowed for the carrier
     *
     * @param null|string $countyDest
     * @param null|string $carrierMethodCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isGirthAllowed($countyDest = null, $carrierMethodCode = null)
    {
        return false;
    }

    /**
     * Set Raw Request
     *
     * @param DataObject|null $request
     * @return $this
     */
    public function setRawRequest($request)
    {
        $this->_rawRequest = $request;

        return $this;
    }

    /**
     * Calculate price considering free shipping and handling fee
     *
     * @param string $cost
     * @param string $method
     * @return float|string
     */
    public function getMethodPrice($cost, $method = '')
    {
        return $method === $this->getConfigData(
            $this->_freeMethod
        ) && $this->getConfigFlag(
            'free_shipping_enable'
        ) && $this->getConfigData(
            'free_shipping_subtotal'
        ) <= $this->_rawRequest->getValueWithDiscount() ? '0.00' : $this->getFinalPriceWithHandlingFee(
            $cost
        );
    }

    /**
     * Parse XML string and return XML document object or false
     *
     * @param string $xmlContent
     * @param string $customSimplexml
     * @return SimpleXMLElement|bool
     * @throws LocalizedException
     */
    public function parseXml($xmlContent, $customSimplexml = 'SimpleXMLElement')
    {
        if (!$this->xmlSecurity->scan($xmlContent)) {
            throw new LocalizedException(__('The security validation of the XML document has failed.'));
        }

        return simplexml_load_string($xmlContent, $customSimplexml);
    }

    /**
     * Checks if shipping method can collect rates
     *
     * @return bool
     */
    public function canCollectRates()
    {
        return (bool)$this->getConfigFlag($this->_activeFlag);
    }

    /**
     * Debug errors if showmethod is unset
     *
     * @param Error $errors
     *
     * @return void
     */
    protected function debugErrors($errors)
    {
        if ($this->getConfigData('showmethod')) {
            /* @var $error Error */
            $this->_debug($errors);
        }
    }

    /**
     * Get error messages
     *
     * @return bool|Error
     */
    protected function getErrorMessage()
    {
        if ($this->getConfigData('showmethod')) {
            /* @var $error Error */
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->getCarrierCode());
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            return $error;
        }

        return false;
    }
}
