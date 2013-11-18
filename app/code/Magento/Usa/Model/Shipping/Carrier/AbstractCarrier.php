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
 * @category    Magento
 * @package     Magento_Usa
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract USA shipping carrier model
 */
namespace Magento\Usa\Model\Shipping\Carrier;

abstract class AbstractCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier
{

    const USA_COUNTRY_ID = 'US';
    const PUERTORICO_COUNTRY_ID = 'PR';
    const GUAM_COUNTRY_ID = 'GU';
    const GUAM_REGION_CODE = 'GU';

    protected static $_quotesCache = array();

    /**
     * Flag for check carriers for activity
     *
     * @var string
     */
    protected $_activeFlag = 'active';

    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryData = null;

    /**
     * @var \Magento\Usa\Model\Simplexml\ElementFactory
     */
    protected $_xmlElFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\Result\MethodFactory
     */
    protected $_rateMethodFactory;


    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    protected $_trackFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\ErrorFactory
     */
    protected $_trackErrorFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    protected $_trackStatusFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * By default is looking for first argument as array and assigns it as object
     * attributes This behavior may change in child classes
     *
     * @param \Magento\Usa\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Shipping\Model\Rate\Result\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Shipping\Model\Rate\Result\ErrorFactory $rateErrorFactory
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Usa\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Shipping\Model\Rate\Result\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Shipping\Model\Rate\Result\ErrorFactory $rateErrorFactory,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        array $data = array()
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
        parent::__construct($coreStoreConfig, $rateErrorFactory, $logAdapterFactory, $data);
    }

    /**
     * Set flag for check carriers for activity
     *
     * @param string $code
     * @return \Magento\Usa\Model\Shipping\Carrier\AbstractCarrier
     */
    public function setActiveFlag($code = 'active')
    {
        $this->_activeFlag = $code;
        return $this;
    }

    /**
     * Return code of carrier
     *
     * @return string
     */
    public function getCarrierCode()
    {
        return isset($this->_code) ? $this->_code : null;
    }

    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
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
     * @param \Magento\Shipping\Model\Rate\Request $request
     * @return array
     */
    public function getAllItems(\Magento\Shipping\Model\Rate\Request $request)
    {
        $items = array();
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /* @var $item \Magento\Sales\Model\Quote\Item */
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
     * @param \Magento\Shipping\Model\Rate\Request $request
     * @return \Magento\Shipping\Model\Carrier\AbstractCarrier|\Magento\Shipping\Model\Rate\Result\Error|boolean
     */
    public function proccessAdditionalValidation(\Magento\Shipping\Model\Rate\Request $request)
    {
        //Skip by item validation if there is no items in request
        if(!count($this->getAllItems($request))) {
            return $this;
        }

        $maxAllowedWeight   = (float) $this->getConfigData('max_package_weight');
        $errorMsg           = '';
        $configErrorMsg     = $this->getConfigData('specificerrmsg');
        $defaultErrorMsg    = __('The shipping module is not available.');
        $showMethod         = $this->getConfigData('showmethod');

        foreach ($this->getAllItems($request) as $item) {
            if ($item->getProduct() && $item->getProduct()->getId()) {
                $weight         = $item->getProduct()->getWeight();
                $stockItem      = $item->getProduct()->getStockItem();
                $doValidation   = true;

                if ($stockItem->getIsQtyDecimal() && $stockItem->getIsDecimalDivided()) {
                    if ($stockItem->getEnableQtyIncrements() && $stockItem->getQtyIncrements()) {
                        $weight = $weight * $stockItem->getQtyIncrements();
                    } else {
                        $doValidation = false;
                    }
                } elseif ($stockItem->getIsQtyDecimal() && !$stockItem->getIsDecimalDivided()) {
                    $weight = $weight * $item->getQty();
                }

                if ($doValidation && $weight > $maxAllowedWeight) {
                    $errorMsg = ($configErrorMsg) ? $configErrorMsg : $defaultErrorMsg;
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
        } elseif ($errorMsg) {
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
            $requestParams = implode(',', array_merge(
                array($this->getCarrierCode()),
                array_keys($requestParams),
                $requestParams)
            );
        }
        return crc32($requestParams);
    }

    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     * Used to reduce number of same requests done to carrier service during one session
     *
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function _getCachedQuotes($requestParams)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        return isset(self::$_quotesCache[$key]) ? self::$_quotesCache[$key] : null;
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return \Magento\Usa\Model\Shipping\Carrier\AbstractCarrier
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
     * @param string|object $name  service name or object with implemented __toString() method
     * @return string              prepared service name
     */
    protected function _prepareServiceName($name)
    {
        $name = html_entity_decode((string)$name);
        $name = strip_tags(preg_replace('#&\w+;#', '', $name));
        return trim($name);
    }

    /**
     * Prepare shipment request.
     * Validate and correct request information
     *
     * @param \Magento\Object $request
     *
     */
    protected function _prepareShipmentRequest(\Magento\Object $request)
    {
        $phonePattern = '/[\s\_\-\(\)]+/';
        $phoneNumber = $request->getShipperContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setShipperContactPhoneNumber($phoneNumber);
        $phoneNumber = $request->getRecipientContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setRecipientContactPhoneNumber($phoneNumber);
    }

    /**
     * Do request to shipment
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array
     */
    public function requestToShipment(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new \Magento\Core\Exception(__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }
        $data = array();
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new \Magento\Object($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->_doShipmentRequest($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $data[] = array(
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $result->getShippingLabelContent()
                );
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new \Magento\Object(array(
            'info'   => $data
        ));
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }
        return $response;
    }

    /**
     * Do request to RMA shipment
     *
     * @param $request
     * @return array
     */
    public function returnOfShipment($request)
    {
        $request->setIsReturn(true);
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new \Magento\Core\Exception(__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }
        $data = array();
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new \Magento\Object($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->_doShipmentRequest($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $data[] = array(
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $result->getShippingLabelContent()
                );
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new \Magento\Object(array(
            'info'   => $data
        ));
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }
        return $response;
    }

    /**
     * For multi package shipments. Delete requested shipments if the current shipment
     * request is failed
     *
     * @todo implement rollback logic
     * @param array $data
     * @return bool
     */
    public function rollBack($data)
    {
        return true;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Object $request
     * @return \Magento\Object
     */
    abstract protected function _doShipmentRequest(\Magento\Object $request);

    /**
     * Check is Country U.S. Possessions and Trust Territories
     *
     * @param string $countyId
     * @return boolean
     */
    protected function _isUSCountry($countyId)
    {
        switch ($countyId) {
            case 'AS': // Samoa American
            case 'GU': // Guam
            case 'MP': // Northern Mariana Islands
            case 'PW': // Palau
            case 'PR': // Puerto Rico
            case 'VI': // Virgin Islands US
            case 'US'; // United States
                return true;
        }

        return false;
    }

    /**
     * Check whether girth is allowed for the carrier
     *
     * @param null|string $countyDest
     * @return bool
     */
    public function isGirthAllowed($countyDest = null)
    {
        return false;
    }
}
