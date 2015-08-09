<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Usps\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;
use Magento\Usps\Helper\Config;

/**
 * USPS shipping
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * USPS containers
     */
    const CONTAINER_VARIABLE = 'VARIABLE';

    const CONTAINER_FLAT_RATE_BOX = 'FLAT RATE BOX';

    const CONTAINER_FLAT_RATE_ENVELOPE = 'FLAT RATE ENVELOPE';

    const CONTAINER_RECTANGULAR = 'RECTANGULAR';

    const CONTAINER_NONRECTANGULAR = 'NONRECTANGULAR';

    /**
     * USPS size
     */
    const SIZE_REGULAR = 'REGULAR';

    const SIZE_LARGE = 'LARGE';

    /**
     * Default api revision
     *
     * @var int
     */
    const DEFAULT_REVISION = 2;

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'usps';

    /**
     * Ounces in one pound for conversion
     */
    const OUNCES_POUND = 16;

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     *
     * @var \Magento\Quote\Model\Quote\Address\RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result = null;

    /**
     * Default cgi gateway url
     *
     * @var string
     */
    protected $_defaultGatewayUrl = 'http://production.shippingapis.com/ShippingAPI.dll';

    /**
     * Container types that could be customized for USPS carrier
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = ['VARIABLE', 'RECTANGULAR', 'NONRECTANGULAR'];

    /**
     * Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $_carrierHelper;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Shipping\Helper\Carrier $carrierHelper
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param Config $configHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        CarrierHelper $carrierHelper,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        Config $configHelper,
        array $data = []
    ) {
        $this->_carrierHelper = $carrierHelper;
        $this->configHelper = $configHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_httpClientFactory = $httpClientFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error|bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $this->setRequest($request);
        $this->_result = $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * Prepare and set request to this instance
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $this->_request = $request;

        $r = new \Magento\Framework\DataObject();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        } else {
            $r->setService('ALL');
        }

        if ($request->getUspsUserid()) {
            $userId = $request->getUspsUserid();
        } else {
            $userId = $this->getConfigData('userid');
        }
        $r->setUserId($userId);

        if ($request->getUspsContainer()) {
            $container = $request->getUspsContainer();
        } else {
            $container = $this->getConfigData('container');
        }
        $r->setContainer($container);

        if ($request->getUspsSize()) {
            $size = $request->getUspsSize();
        } else {
            $size = $this->getConfigData('size');
        }
        $r->setSize($size);

        if ($request->getGirth()) {
            $girth = $request->getGirth();
        } else {
            $girth = $this->getConfigData('girth');
        }
        $r->setGirth($girth);

        if ($request->getHeight()) {
            $height = $request->getHeight();
        } else {
            $height = $this->getConfigData('height');
        }
        $r->setHeight($height);

        if ($request->getLength()) {
            $length = $request->getLength();
        } else {
            $length = $this->getConfigData('length');
        }
        $r->setLength($length);

        if ($request->getWidth()) {
            $width = $request->getWidth();
        } else {
            $width = $this->getConfigData('width');
        }
        $r->setWidth($width);

        if ($request->getUspsMachinable()) {
            $machinable = $request->getUspsMachinable();
        } else {
            $machinable = $this->getConfigData('machinable');
        }
        $r->setMachinable($machinable);

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getOrigCountryId()) {
            $r->setOrigCountryId($request->getOrigCountryId());
        } else {
            $r->setOrigCountryId(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        $r->setDestCountryId($destCountry);

        if (!$this->_isUSCountry($destCountry)) {
            $r->setDestCountryName($this->_getCountryName($destCountry));
        }

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeightPounds(floor($weight));
        $r->setWeightOunces(round(($weight - floor($weight)) * self::OUNCES_POUND, 1));
        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackageValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->setRawRequest($r);

        return $this;
    }

    /**
     * Get result of request
     *
     * @return Result|null
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Get quotes
     *
     * @return Result
     */
    protected function _getQuotes()
    {
        return $this->_getXmlQuotes();
    }

    /**
     * Set free method request
     *
     * @param string $freeMethod
     * @return void
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $r->setWeightPounds(floor($weight));
        $r->setWeightOunces(round(($weight - floor($weight)) * self::OUNCES_POUND, 1));
        $r->setService($freeMethod);
    }

    /**
     * Build RateV3 request, send it to USPS gateway and retrieve quotes in XML format
     *
     * @link http://www.usps.com/webtools/htm/Rate-Calculators-v2-3.htm
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getXmlQuotes()
    {
        $r = $this->_rawRequest;

        // The origin address(shipper) must be only in USA
        if (!$this->_isUSCountry($r->getOrigCountryId())) {
            $responseBody = '';

            return $this->_parseXmlResponse($responseBody);
        }

        if ($this->_isUSCountry($r->getDestCountryId())) {
            $xml = $this->_xmlElFactory->create(
                ['data' => '<?xml version="1.0" encoding="UTF-8"?><RateV4Request/>']
            );
            $xml->addAttribute('USERID', $r->getUserId());
            // according to usps v4 documentation
            $xml->addChild('Revision', '2');

            $package = $xml->addChild('Package');
            $package->addAttribute('ID', 0);
            $service = $this->configHelper->getCode('service_to_code', $r->getService());
            if (!$service) {
                $service = $r->getService();
            }
            if ($r->getContainer() == 'FLAT RATE BOX' || $r->getContainer() == 'FLAT RATE ENVELOPE') {
                $service = 'Priority';
            }
            $package->addChild('Service', $service);

            // no matter Letter, Flat or Parcel, use Parcel
            if ($r->getService() == 'FIRST CLASS' || $r->getService() == 'FIRST CLASS HFP COMMERCIAL') {
                $package->addChild('FirstClassMailType', 'PARCEL');
            }
            $package->addChild('ZipOrigination', $r->getOrigPostal());
            //only 5 chars avaialble
            $package->addChild('ZipDestination', substr($r->getDestPostal(), 0, 5));
            $package->addChild('Pounds', $r->getWeightPounds());
            $package->addChild('Ounces', $r->getWeightOunces());
            // Because some methods don't accept VARIABLE and (NON)RECTANGULAR containers
            $package->addChild('Container', $r->getContainer());
            $package->addChild('Size', $r->getSize());
            if ($r->getSize() == 'LARGE') {
                $package->addChild('Width', $r->getWidth());
                $package->addChild('Length', $r->getLength());
                $package->addChild('Height', $r->getHeight());
                if ($r->getContainer() == 'NONRECTANGULAR' || $r->getContainer() == 'VARIABLE') {
                    $package->addChild('Girth', $r->getGirth());
                }
            }
            $package->addChild('Machinable', $r->getMachinable());

            $api = 'RateV4';
        } else {
            $xml = $this->_xmlElFactory->create(
                ['data' => '<?xml version = "1.0" encoding = "UTF-8"?><IntlRateV2Request/>']
            );
            $xml->addAttribute('USERID', $r->getUserId());
            // according to usps v4 documentation
            $xml->addChild('Revision', '2');

            $package = $xml->addChild('Package');
            $package->addAttribute('ID', 0);
            $package->addChild('Pounds', $r->getWeightPounds());
            $package->addChild('Ounces', $r->getWeightOunces());
            $package->addChild('MailType', 'All');
            $package->addChild('ValueOfContents', $r->getValue());
            $package->addChild('Country', $r->getDestCountryName());
            $package->addChild('Container', $r->getContainer());
            $package->addChild('Size', $r->getSize());
            $width = $length = $height = $girth = '';
            if ($r->getSize() == 'LARGE') {
                $width = $r->getWidth();
                $length = $r->getLength();
                $height = $r->getHeight();
                if ($r->getContainer() == 'NONRECTANGULAR') {
                    $girth = $r->getGirth();
                }
            }
            $package->addChild('Width', $width);
            $package->addChild('Length', $length);
            $package->addChild('Height', $height);
            $package->addChild('Girth', $girth);

            $api = 'IntlRateV2';
        }
        $request = $xml->asXML();

        $responseBody = $this->_getCachedQuotes($request);
        if ($responseBody === null) {
            $debugData = ['request' => $request];
            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultGatewayUrl;
                }
                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                $client->setParameterGet('API', $api);
                $client->setParameterGet('XML', $request);
                $response = $client->request();
                $responseBody = $response->getBody();

                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($request, $responseBody);
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $responseBody = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseXmlResponse($responseBody);
    }

    /**
     * Parse calculated rates
     *
     * @param string $response
     * @return Result
     * @link http://www.usps.com/webtools/htm/Rate-Calculators-v2-3.htm
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parseXmlResponse($response)
    {
        $r = $this->_rawRequest;
        $costArr = [];
        $priceArr = [];
        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                if (strpos($response, '<?xml version="1.0"?>') !== false) {
                    $response = str_replace(
                        '<?xml version="1.0"?>',
                        '<?xml version="1.0" encoding="ISO-8859-1"?>',
                        $response
                    );
                }
                $xml = $this->parseXml($response);

                if (is_object($xml)) {
                    $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));
                    $serviceCodeToActualNameMap = [];
                    /**
                     * US Rates
                     */
                    if ($this->_isUSCountry($r->getDestCountryId())) {
                        if (is_object($xml->Package) && is_object($xml->Package->Postage)) {
                            foreach ($xml->Package->Postage as $postage) {
                                $serviceName = $this->_filterServiceName((string)$postage->MailService);
                                $_serviceCode = $this->configHelper->getCode('method_to_code', $serviceName);
                                $serviceCode = $_serviceCode ? $_serviceCode : (string)$postage->attributes()->CLASSID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                if (in_array($serviceCode, $allowedMethods)) {
                                    $costArr[$serviceCode] = (string)$postage->Rate;
                                    $priceArr[$serviceCode] = $this->getMethodPrice(
                                        (string)$postage->Rate,
                                        $serviceCode
                                    );
                                }
                            }
                            asort($priceArr);
                        }
                    } else {
                        /*
                         * International Rates
                         */
                        if (is_object($xml->Package) && is_object($xml->Package->Service)) {
                            foreach ($xml->Package->Service as $service) {
                                $serviceName = $this->_filterServiceName((string)$service->SvcDescription);
                                $serviceCode = 'INT_' . (string)$service->attributes()->ID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                if (in_array($serviceCode, $allowedMethods)) {
                                    $costArr[$serviceCode] = (string)$service->Postage;
                                    $priceArr[$serviceCode] = $this->getMethodPrice(
                                        (string)$service->Postage,
                                        $serviceCode
                                    );
                                }
                            }
                            asort($priceArr);
                        }
                    }
                }
            }
        }

        $result = $this->_rateFactory->create();
        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('usps');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('usps');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle(
                    isset(
                        $serviceCodeToActualNameMap[$method]
                    ) ? $serviceCodeToActualNameMap[$method] : $this->configHelper->getCode(
                        'method',
                        $method
                    )
                );
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $this->_getXmlTracking($trackings);

        return $this->_result;
    }

    /**
     * Set tracking request
     *
     * @return void
     */
    protected function setTrackingReqeust()
    {
        $r = new \Magento\Framework\DataObject();

        $userId = $this->getConfigData('userid');
        $r->setUserId($userId);

        $this->_rawTrackRequest = $r;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $trackings
     * @return void
     */
    protected function _getXmlTracking($trackings)
    {
        $r = $this->_rawTrackRequest;

        foreach ($trackings as $tracking) {
            $xml = $this->_xmlElFactory->create(
                ['data' => '<?xml version = "1.0" encoding = "UTF-8"?><TrackRequest/>']
            );
            $xml->addAttribute('USERID', $r->getUserId());

            $trackid = $xml->addChild('TrackID');
            $trackid->addAttribute('ID', $tracking);

            $api = 'TrackV2';
            $request = $xml->asXML();
            $debugData = ['request' => $request];

            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultGatewayUrl;
                }
                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                $client->setParameterGet('API', $api);
                $client->setParameterGet('XML', $request);
                $response = $client->request();
                $responseBody = $response->getBody();
                $debugData['result'] = $responseBody;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $responseBody = '';
            }

            $this->_debug($debugData);
            $this->_parseXmlTrackingResponse($tracking, $responseBody);
        }
    }

    /**
     * Parse xml tracking response
     *
     * @param string $trackingvalue
     * @param string $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _parseXmlTrackingResponse($trackingvalue, $response)
    {
        $errorTitle = __('For some reason we can\'t retrieve tracking info right now.');
        $resultArr = [];
        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                $xml = $this->parseXml($response);
                if (is_object($xml)) {
                    if (isset($xml->Number) && isset($xml->Description) && (string)$xml->Description != '') {
                        $errorTitle = (string)$xml->Description;
                    } elseif (isset(
                            $xml->TrackInfo
                        ) && isset(
                            $xml->TrackInfo->Error
                        ) && isset(
                            $xml->TrackInfo->Error->Description
                        ) && (string)$xml->TrackInfo->Error->Description != ''
                    ) {
                        $errorTitle = (string)$xml->TrackInfo->Error->Description;
                    } else {
                        $errorTitle = __(
                            'Sorry, something went wrong. Please try again or contact us and we\'ll try to help.'
                        );
                    }

                    if (isset($xml->TrackInfo) && isset($xml->TrackInfo->TrackSummary)) {
                        $resultArr['tracksummary'] = (string)$xml->TrackInfo->TrackSummary;
                    }
                }
            }
        }

        if (!$this->_result) {
            $this->_result = $this->_trackFactory->create();
        }
        $defaults = $this->getDefaults();

        if ($resultArr) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier('usps');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            $tracking->setTrackSummary($resultArr['tracksummary']);
            $this->_result->append($tracking);
        } else {
            $error = $this->_trackErrorFactory->create();
            $error->setCarrier('usps');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $this->_result->append($error);
        }
    }

    /**
     * Get tracking response
     *
     * @return string
     */
    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof \Magento\Shipping\Model\Tracking\Result) {
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking) {
                    if ($data = $tracking->getAllData()) {
                        if (!empty($data['track_summary'])) {
                            $statuses .= __($data['track_summary']);
                        } else {
                            $statuses .= __('Empty response');
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = __('Empty response');
        }

        return $statuses;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->configHelper->getCode('method', $k);
        }

        return $arr;
    }

    /**
     * Return USPS county name by country ISO 3166-1-alpha-2 code
     * Return false for unknown countries
     *
     * @param string $countryId
     * @return string|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getCountryName($countryId)
    {
        $countries = [
            'AD' => 'Andorra',
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AG' => 'Antigua and Barbuda',
            'AI' => 'Anguilla',
            'AL' => 'Albania',
            'AM' => 'Armenia',
            'AN' => 'Netherlands Antilles',
            'AO' => 'Angola',
            'AR' => 'Argentina',
            'AT' => 'Austria',
            'AU' => 'Australia',
            'AW' => 'Aruba',
            'AX' => 'Aland Island (Finland)',
            'AZ' => 'Azerbaijan',
            'BA' => 'Bosnia-Herzegovina',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BN' => 'Brunei Darussalam',
            'BO' => 'Bolivia',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BW' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CC' => 'Cocos Island (Australia)',
            'CD' => 'Congo, Democratic Republic of the',
            'CF' => 'Central African Republic',
            'CG' => 'Congo, Republic of the',
            'CH' => 'Switzerland',
            'CI' => 'Ivory Coast (Cote d Ivoire)',
            'CK' => 'Cook Islands (New Zealand)',
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CV' => 'Cape Verde',
            'CX' => 'Christmas Island (Australia)',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DE' => 'Germany',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'DZ' => 'Algeria',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'ER' => 'Eritrea',
            'ES' => 'Spain',
            'ET' => 'Ethiopia',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Islands',
            'FM' => 'Micronesia, Federated States of',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GB' => 'Great Britain and Northern Ireland',
            'GD' => 'Grenada',
            'GE' => 'Georgia, Republic of',
            'GF' => 'French Guiana',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Equatorial Guinea',
            'GR' => 'Greece',
            'GS' => 'South Georgia (Falkland Islands)',
            'GT' => 'Guatemala',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'Hong Kong',
            'HN' => 'Honduras',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IN' => 'India',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KE' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'Saint Kitts (Saint Christopher and Nevis)',
            'KP' => 'North Korea (Korea, Democratic People\'s Republic of)',
            'KR' => 'South Korea (Korea, Republic of)',
            'KW' => 'Kuwait',
            'KY' => 'Cayman Islands',
            'KZ' => 'Kazakhstan',
            'LA' => 'Laos',
            'LB' => 'Lebanon',
            'LC' => 'Saint Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MC' => 'Monaco (France)',
            'MD' => 'Moldova',
            'MG' => 'Madagascar',
            'MK' => 'Macedonia, Republic of',
            'ML' => 'Mali',
            'MM' => 'Burma',
            'MN' => 'Mongolia',
            'MO' => 'Macao',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NZ' => 'New Zealand',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'Saint Pierre and Miquelon',
            'PN' => 'Pitcairn Island',
            'PT' => 'Portugal',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SB' => 'Solomon Islands',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SH' => 'Saint Helena',
            'SI' => 'Slovenia',
            'SK' => 'Slovak Republic',
            'SL' => 'Sierra Leone',
            'SM' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SR' => 'Suriname',
            'ST' => 'Sao Tome and Principe',
            'SV' => 'El Salvador',
            'SY' => 'Syrian Arab Republic',
            'SZ' => 'Swaziland',
            'TC' => 'Turks and Caicos Islands',
            'TD' => 'Chad',
            'TG' => 'Togo',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TK' => 'Tokelau (Union Group) (Western Samoa)',
            'TL' => 'East Timor (Timor-Leste, Democratic Republic of)',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TR' => 'Turkey',
            'TT' => 'Trinidad and Tobago',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TZ' => 'Tanzania',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VA' => 'Vatican City',
            'VC' => 'Saint Vincent and the Grenadines',
            'VE' => 'Venezuela',
            'VG' => 'British Virgin Islands',
            'VN' => 'Vietnam',
            'VU' => 'Vanuatu',
            'WF' => 'Wallis and Futuna Islands',
            'WS' => 'Western Samoa',
            'YE' => 'Yemen',
            'YT' => 'Mayotte (France)',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'US' => 'United States',
        ];

        if (isset($countries[$countryId])) {
            return $countries[$countryId];
        }

        return false;
    }

    /**
     * Clean service name from unsupported strings and characters
     *
     * @param  string $name
     * @return string
     */
    protected function _filterServiceName($name)
    {
        $name = (string)preg_replace(
            ['~<[^/!][^>]+>.*</[^>]+>~sU', '~\<!--.*--\>~isU', '~<[^>]+>~is'],
            '',
            html_entity_decode($name)
        );
        $name = str_replace('*', '', $name);

        return $name;
    }

    /**
     * Form XML for US shipment request
     * As integration guide it is important to follow appropriate sequence for tags e.g.: <FromLastName /> must be
     * after <FromFirstName />
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     */
    protected function _formUsExpressShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $packageParams = $request->getPackageParams();

        $packageWeight = $request->getPackageWeight();
        if ($packageParams->getWeightUnits() != \Zend_Measure_Weight::OUNCE) {
            $packageWeight = round(
                $this->_carrierHelper->convertMeasureWeight(
                    $request->getPackageWeight(),
                    $packageParams->getWeightUnits(),
                    \Zend_Measure_Weight::OUNCE
                )
            );
        }

        list($fromZip5, $fromZip4) = $this->_parseZip($request->getShipperAddressPostalCode());
        list($toZip5, $toZip4) = $this->_parseZip($request->getRecipientAddressPostalCode(), true);

        $rootNode = 'ExpressMailLabelRequest';
        // the wrap node needs for remove xml declaration above
        $xmlWrap = $this->_xmlElFactory->create(['data' => '<?xml version = "1.0" encoding = "UTF-8"?><wrap/>']);
        $xml = $xmlWrap->addChild($rootNode);
        $xml->addAttribute('USERID', $this->getConfigData('userid'));
        $xml->addAttribute('PASSWORD', $this->getConfigData('password'));
        $xml->addChild('Option');
        $xml->addChild('Revision');
        $xml->addChild('EMCAAccount');
        $xml->addChild('EMCAPassword');
        $xml->addChild('ImageParameters');
        $xml->addChild('FromFirstName', $request->getShipperContactPersonFirstName());
        $xml->addChild('FromLastName', $request->getShipperContactPersonLastName());
        $xml->addChild('FromFirm', $request->getShipperContactCompanyName());
        $xml->addChild('FromAddress1', $request->getShipperAddressStreet2());
        $xml->addChild('FromAddress2', $request->getShipperAddressStreet1());
        $xml->addChild('FromCity', $request->getShipperAddressCity());
        $xml->addChild('FromState', $request->getShipperAddressStateOrProvinceCode());
        $xml->addChild('FromZip5', $fromZip5);
        $xml->addChild('FromZip4', $fromZip4);
        $xml->addChild('FromPhone', $request->getShipperContactPhoneNumber());
        $xml->addChild('ToFirstName', $request->getRecipientContactPersonFirstName());
        $xml->addChild('ToLastName', $request->getRecipientContactPersonLastName());
        $xml->addChild('ToFirm', $request->getRecipientContactCompanyName());
        $xml->addChild('ToAddress1', $request->getRecipientAddressStreet2());
        $xml->addChild('ToAddress2', $request->getRecipientAddressStreet1());
        $xml->addChild('ToCity', $request->getRecipientAddressCity());
        $xml->addChild('ToState', $request->getRecipientAddressStateOrProvinceCode());
        $xml->addChild('ToZip5', $toZip5);
        $xml->addChild('ToZip4', $toZip4);
        $xml->addChild('ToPhone', $request->getRecipientContactPhoneNumber());
        $xml->addChild('WeightInOunces', $packageWeight);
        $xml->addChild('WaiverOfSignature', $packageParams->getDeliveryConfirmation());
        $xml->addChild('POZipCode');
        $xml->addChild('ImageType', 'PDF');

        $xml = $xmlWrap->{$rootNode}->asXML();

        return $xml;
    }

    /**
     * Form XML for US Signature Confirmation request
     * As integration guide it is important to follow appropriate sequence for tags e.g.: <FromLastName /> must be
     * after <FromFirstName />
     *
     * @param \Magento\Framework\DataObject $request
     * @param string $serviceType
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _formUsSignatureConfirmationShipmentRequest(\Magento\Framework\DataObject $request, $serviceType)
    {
        switch ($serviceType) {
            case 'PRIORITY':
            case 'Priority':
                $serviceType = 'Priority';
                break;
            case 'FIRST CLASS':
            case 'First Class':
                $serviceType = 'First Class';
                break;
            case 'STANDARD':
            case 'Standard Post':
                $serviceType = 'Standard Post';
                break;
            case 'MEDIA':
            case 'Media':
                $serviceType = 'Media Mail';
                break;
            case 'LIBRARY':
            case 'Library':
                $serviceType = 'Library Mail';
                break;
            default:
                throw new \Exception(__('Service type does not match'));
        }
        $packageParams = $request->getPackageParams();
        $packageWeight = $request->getPackageWeight();
        if ($packageParams->getWeightUnits() != \Zend_Measure_Weight::OUNCE) {
            $packageWeight = round(
                $this->_carrierHelper->convertMeasureWeight(
                    $request->getPackageWeight(),
                    $packageParams->getWeightUnits(),
                    \Zend_Measure_Weight::OUNCE
                )
            );
        }

        list($fromZip5, $fromZip4) = $this->_parseZip($request->getShipperAddressPostalCode());
        list($toZip5, $toZip4) = $this->_parseZip($request->getRecipientAddressPostalCode(), true);

        if ($this->getConfigData('mode')) {
            $rootNode = 'SignatureConfirmationV3.0Request';
        } else {
            $rootNode = 'SigConfirmCertifyV3.0Request';
        }
        // the wrap node needs for remove xml declaration above
        $xmlWrap = $this->_xmlElFactory->create(['data' => '<?xml version = "1.0" encoding = "UTF-8"?><wrap/>']);
        $xml = $xmlWrap->addChild($rootNode);
        $xml->addAttribute('USERID', $this->getConfigData('userid'));
        $xml->addChild('Option', 1);
        $xml->addChild('ImageParameters');
        $xml->addChild('FromName', $request->getShipperContactPersonName());
        $xml->addChild('FromFirm', $request->getShipperContactCompanyName());
        $xml->addChild('FromAddress1', $request->getShipperAddressStreet2());
        $xml->addChild('FromAddress2', $request->getShipperAddressStreet1());
        $xml->addChild('FromCity', $request->getShipperAddressCity());
        $xml->addChild('FromState', $request->getShipperAddressStateOrProvinceCode());
        $xml->addChild('FromZip5', $fromZip5);
        $xml->addChild('FromZip4', $fromZip4);
        $xml->addChild('ToName', $request->getRecipientContactPersonName());
        $xml->addChild('ToFirm', $request->getRecipientContactCompanyName());
        $xml->addChild('ToAddress1', $request->getRecipientAddressStreet2());
        $xml->addChild('ToAddress2', $request->getRecipientAddressStreet1());
        $xml->addChild('ToCity', $request->getRecipientAddressCity());
        $xml->addChild('ToState', $request->getRecipientAddressStateOrProvinceCode());
        $xml->addChild('ToZip5', $toZip5);
        $xml->addChild('ToZip4', $toZip4);
        $xml->addChild('WeightInOunces', $packageWeight);
        $xml->addChild('ServiceType', $serviceType);
        $xml->addChild('WaiverOfSignature', $packageParams->getDeliveryConfirmation());
        $xml->addChild('ImageType', 'PDF');

        $xml = $xmlWrap->{$rootNode}->asXML();

        return $xml;
    }

    /**
     * Convert decimal weight into pound-ounces format
     *
     * @param float $weightInPounds
     * @return float[]
     */
    protected function _convertPoundOunces($weightInPounds)
    {
        $weightInOunces = ceil($weightInPounds * self::OUNCES_POUND);
        $pounds = floor($weightInOunces / self::OUNCES_POUND);
        $ounces = $weightInOunces % self::OUNCES_POUND;

        return [$pounds, $ounces];
    }

    /**
     * Form XML for international shipment request
     * As integration guide it is important to follow appropriate sequence for tags e.g.: <FromLastName /> must be
     * after <FromFirstName />
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formIntlShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $packageParams = $request->getPackageParams();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $girth = $packageParams->getGirth();
        $packageWeight = $request->getPackageWeight();
        if ($packageParams->getWeightUnits() != \Zend_Measure_Weight::POUND) {
            $packageWeight = $this->_carrierHelper->convertMeasureWeight(
                $request->getPackageWeight(),
                $packageParams->getWeightUnits(),
                \Zend_Measure_Weight::POUND
            );
        }
        if ($packageParams->getDimensionUnits() != \Zend_Measure_Length::INCH) {
            $length = round(
                $this->_carrierHelper->convertMeasureDimension(
                    $packageParams->getLength(),
                    $packageParams->getDimensionUnits(),
                    \Zend_Measure_Length::INCH
                )
            );
            $width = round(
                $this->_carrierHelper->convertMeasureDimension(
                    $packageParams->getWidth(),
                    $packageParams->getDimensionUnits(),
                    \Zend_Measure_Length::INCH
                )
            );
            $height = round(
                $this->_carrierHelper->convertMeasureDimension(
                    $packageParams->getHeight(),
                    $packageParams->getDimensionUnits(),
                    \Zend_Measure_Length::INCH
                )
            );
        }
        if ($packageParams->getGirthDimensionUnits() != \Zend_Measure_Length::INCH) {
            $girth = round(
                $this->_carrierHelper->convertMeasureDimension(
                    $packageParams->getGirth(),
                    $packageParams->getGirthDimensionUnits(),
                    \Zend_Measure_Length::INCH
                )
            );
        }

        $container = $request->getPackagingType();
        switch ($container) {
            case 'VARIABLE':
                $container = 'VARIABLE';
                break;
            case 'FLAT RATE ENVELOPE':
                $container = 'FLATRATEENV';
                break;
            case 'FLAT RATE BOX':
                $container = 'FLATRATEBOX';
                break;
            case 'RECTANGULAR':
                $container = 'RECTANGULAR';
                break;
            case 'NONRECTANGULAR':
                $container = 'NONRECTANGULAR';
                break;
            default:
                $container = 'VARIABLE';
        }
        $shippingMethod = $request->getShippingMethod();
        list($fromZip5, $fromZip4) = $this->_parseZip($request->getShipperAddressPostalCode());

        // the wrap node needs for remove xml declaration above
        $xmlWrap = $this->_xmlElFactory->create(['data' => '<?xml version = "1.0" encoding = "UTF-8"?><wrap/>']);
        $method = '';
        $service = $this->configHelper->getCode('service_to_code', $shippingMethod);
        if ($service == 'Priority') {
            $method = 'Priority';
            $rootNode = 'PriorityMailIntlRequest';
            $xml = $xmlWrap->addChild($rootNode);
        } else {
            if ($service == 'First Class') {
                $method = 'FirstClass';
                $rootNode = 'FirstClassMailIntlRequest';
                $xml = $xmlWrap->addChild($rootNode);
            } else {
                $method = 'Express';
                $rootNode = 'ExpressMailIntlRequest';
                $xml = $xmlWrap->addChild($rootNode);
            }
        }

        $xml->addAttribute('USERID', $this->getConfigData('userid'));
        $xml->addAttribute('PASSWORD', $this->getConfigData('password'));
        $xml->addChild('Option');
        $xml->addChild('Revision', self::DEFAULT_REVISION);
        $xml->addChild('ImageParameters');
        $xml->addChild('FromFirstName', $request->getShipperContactPersonFirstName());
        $xml->addChild('FromLastName', $request->getShipperContactPersonLastName());
        $xml->addChild('FromFirm', $request->getShipperContactCompanyName());
        $xml->addChild('FromAddress1', $request->getShipperAddressStreet2());
        $xml->addChild('FromAddress2', $request->getShipperAddressStreet1());
        $xml->addChild('FromCity', $request->getShipperAddressCity());
        $xml->addChild('FromState', $request->getShipperAddressStateOrProvinceCode());
        $xml->addChild('FromZip5', $fromZip5);
        $xml->addChild('FromZip4', $fromZip4);
        $xml->addChild('FromPhone', $request->getShipperContactPhoneNumber());
        if ($method != 'FirstClass') {
            if ($request->getReferenceData()) {
                $referenceData = $request->getReferenceData() . ' P' . $request->getPackageId();
            } else {
                $referenceData = $request->getOrderShipment()->getOrder()->getIncrementId() .
                    ' P' .
                    $request->getPackageId();
            }
            $xml->addChild('FromCustomsReference', 'Order #' . $referenceData);
        }
        $xml->addChild('ToFirstName', $request->getRecipientContactPersonFirstName());
        $xml->addChild('ToLastName', $request->getRecipientContactPersonLastName());
        $xml->addChild('ToFirm', $request->getRecipientContactCompanyName());
        $xml->addChild('ToAddress1', $request->getRecipientAddressStreet1());
        $xml->addChild('ToAddress2', $request->getRecipientAddressStreet2());
        $xml->addChild('ToCity', $request->getRecipientAddressCity());
        $xml->addChild('ToProvince', $request->getRecipientAddressStateOrProvinceCode());
        $xml->addChild('ToCountry', $this->_getCountryName($request->getRecipientAddressCountryCode()));
        $xml->addChild('ToPostalCode', $request->getRecipientAddressPostalCode());
        $xml->addChild('ToPOBoxFlag', 'N');
        $xml->addChild('ToPhone', $request->getRecipientContactPhoneNumber());
        $xml->addChild('ToFax');
        $xml->addChild('ToEmail');
        if ($method != 'FirstClass') {
            $xml->addChild('NonDeliveryOption', 'Return');
        }
        if ($method == 'FirstClass') {
            if (stripos($shippingMethod, 'Letter') !== false) {
                $xml->addChild('FirstClassMailType', 'LETTER');
            } else {
                if (stripos($shippingMethod, 'Flat') !== false) {
                    $xml->addChild('FirstClassMailType', 'FLAT');
                } else {
                    $xml->addChild('FirstClassMailType', 'PARCEL');
                }
            }
        }
        if ($method != 'FirstClass') {
            $xml->addChild('Container', $container);
        }
        $shippingContents = $xml->addChild('ShippingContents');
        $packageItems = $request->getPackageItems();
        // get countries of manufacture
        $countriesOfManufacture = [];
        $productIds = [];
        foreach ($packageItems as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);

            $productIds[] = $item->getProductId();
        }
        $productCollection = $this->_productCollectionFactory->create()->addStoreFilter(
            $request->getStoreId()
        )->addFieldToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToSelect(
            'country_of_manufacture'
        );
        foreach ($productCollection as $product) {
            $countriesOfManufacture[$product->getId()] = $product->getCountryOfManufacture();
        }

        $packagePoundsWeight = $packageOuncesWeight = 0;
        // for ItemDetail
        foreach ($packageItems as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);

            $itemWeight = $item->getWeight() * $item->getQty();
            if ($packageParams->getWeightUnits() != \Zend_Measure_Weight::POUND) {
                $itemWeight = $this->_carrierHelper->convertMeasureWeight(
                    $itemWeight,
                    $packageParams->getWeightUnits(),
                    \Zend_Measure_Weight::POUND
                );
            }
            if (!empty($countriesOfManufacture[$item->getProductId()])) {
                $countryOfManufacture = $this->_getCountryName($countriesOfManufacture[$item->getProductId()]);
            } else {
                $countryOfManufacture = '';
            }
            $itemDetail = $shippingContents->addChild('ItemDetail');
            $itemDetail->addChild('Description', $item->getName());
            $ceiledQty = ceil($item->getQty());
            if ($ceiledQty < 1) {
                $ceiledQty = 1;
            }
            $individualItemWeight = $itemWeight / $ceiledQty;
            $itemDetail->addChild('Quantity', $ceiledQty);
            $itemDetail->addChild('Value', $item->getCustomsValue() * $item->getQty());
            list($individualPoundsWeight, $individualOuncesWeight) = $this->_convertPoundOunces($individualItemWeight);
            $itemDetail->addChild('NetPounds', $individualPoundsWeight);
            $itemDetail->addChild('NetOunces', $individualOuncesWeight);
            $itemDetail->addChild('HSTariffNumber', 0);
            $itemDetail->addChild('CountryOfOrigin', $countryOfManufacture);

            list($itemPoundsWeight, $itemOuncesWeight) = $this->_convertPoundOunces($itemWeight);
            $packagePoundsWeight += $itemPoundsWeight;
            $packageOuncesWeight += $itemOuncesWeight;
        }
        $additionalPackagePoundsWeight = floor($packageOuncesWeight / self::OUNCES_POUND);
        $packagePoundsWeight += $additionalPackagePoundsWeight;
        $packageOuncesWeight -= $additionalPackagePoundsWeight * self::OUNCES_POUND;
        if ($packagePoundsWeight + $packageOuncesWeight / self::OUNCES_POUND < $packageWeight) {
            list($packagePoundsWeight, $packageOuncesWeight) = $this->_convertPoundOunces($packageWeight);
        }

        $xml->addChild('GrossPounds', $packagePoundsWeight);
        $xml->addChild('GrossOunces', $packageOuncesWeight);
        if ($packageParams->getContentType() == 'OTHER' && $packageParams->getContentTypeOther() != null) {
            $xml->addChild('ContentType', $packageParams->getContentType());
            $xml->addChild('ContentTypeOther ', $packageParams->getContentTypeOther());
        } else {
            $xml->addChild('ContentType', $packageParams->getContentType());
        }

        $xml->addChild('Agreement', 'y');
        $xml->addChild('ImageType', 'PDF');
        $xml->addChild('ImageLayout', 'ALLINONEFILE');
        if ($method == 'FirstClass') {
            $xml->addChild('Container', $container);
        }
        // set size
        if ($packageParams->getSize()) {
            $xml->addChild('Size', $packageParams->getSize());
        }
        // set dimensions
        $xml->addChild('Length', $length);
        $xml->addChild('Width', $width);
        $xml->addChild('Height', $height);
        if ($girth) {
            $xml->addChild('Girth', $girth);
        }

        $xml = $xmlWrap->{$rootNode}->asXML();

        return $xml;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();
        $service = $this->configHelper->getCode('service_to_code', $request->getShippingMethod());
        $recipientUSCountry = $this->_isUSCountry($request->getRecipientAddressCountryCode());

        if ($recipientUSCountry && $service == 'Priority Express') {
            $requestXml = $this->_formUsExpressShipmentRequest($request);
            $api = 'ExpressMailLabel';
        } else {
            if ($recipientUSCountry) {
                $requestXml = $this->_formUsSignatureConfirmationShipmentRequest($request, $service);
                if ($this->getConfigData('mode')) {
                    $api = 'SignatureConfirmationV3';
                } else {
                    $api = 'SignatureConfirmationCertifyV3';
                }
            } else {
                if ($service == 'First Class') {
                    $requestXml = $this->_formIntlShipmentRequest($request);
                    $api = 'FirstClassMailIntl';
                } else {
                    if ($service == 'Priority') {
                        $requestXml = $this->_formIntlShipmentRequest($request);
                        $api = 'PriorityMailIntl';
                    } else {
                        $requestXml = $this->_formIntlShipmentRequest($request);
                        $api = 'ExpressMailIntl';
                    }
                }
            }
        }

        $debugData = ['request' => $requestXml];
        $url = $this->getConfigData('gateway_secure_url');
        if (!$url) {
            $url = $this->_defaultGatewayUrl;
        }
        $client = $this->_httpClientFactory->create();
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setParameterGet('API', $api);
        $client->setParameterGet('XML', $requestXml);
        $response = $client->request()->getBody();

        $response = $this->parseXml($response);
        if ($response === false || $response->getName() == 'Error') {
            $debugData['result'] = [
                'error' => $response->Description,
                'code' => $response->Number,
                'xml' => $response->asXML(),
            ];
            $this->_debug($debugData);
            $result->setErrors($debugData['result']['error']);
        } else {
            if ($recipientUSCountry && $service == 'Priority Express') {
                $labelContent = base64_decode((string)$response->EMLabel);
                $trackingNumber = (string)$response->EMConfirmationNumber;
            } elseif ($recipientUSCountry) {
                $labelContent = base64_decode((string)$response->SignatureConfirmationLabel);
                $trackingNumber = (string)$response->SignatureConfirmationNumber;
            } else {
                $labelContent = base64_decode((string)$response->LabelImage);
                $trackingNumber = (string)$response->BarcodeNumber;
            }
            $result->setShippingLabelContent($labelContent);
            $result->setTrackingNumber($trackingNumber);
        }

        $result->setGatewayResponse($response);
        $debugData['result'] = $response;
        $this->_debug($debugData);

        return $result;
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array|bool
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        if (is_null($params)) {
            return $this->_getAllowedContainers();
        }

        return $this->_isUSCountry($params->getCountryRecipient()) ? [] : $this->_getAllowedContainers($params);
    }

    /**
     * Return all container types of carrier
     *
     * @return array|bool
     */
    public function getContainerTypesAll()
    {
        return $this->configHelper->getCode('container');
    }

    /**
     * Return structured data of containers witch related with shipping methods
     *
     * @return array|bool
     */
    public function getContainerTypesFilter()
    {
        return $this->configHelper->getCode('containers_filter');
    }

    /**
     * Return delivery confirmation types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\DataObject $params = null)
    {
        if ($params == null) {
            return [];
        }
        $countryRecipient = $params->getCountryRecipient();
        if ($this->_isUSCountry($countryRecipient)) {
            return $this->configHelper->getCode('delivery_confirmation_types');
        } else {
            return [];
        }
    }

    /**
     * Check whether girth is allowed for the USPS
     *
     * @param null|string $countyDest
     * @return bool
     */
    public function isGirthAllowed($countyDest = null)
    {
        return $this->_isUSCountry($countyDest) ? false : true;
    }

    /**
     * Return content types of package
     *
     * @param \Magento\Framework\DataObject $params
     * @return array
     */
    public function getContentTypes(\Magento\Framework\DataObject $params)
    {
        $countryShipper = $params->getCountryShipper();
        $countryRecipient = $params->getCountryRecipient();

        if ($countryShipper == self::USA_COUNTRY_ID && $countryRecipient != self::USA_COUNTRY_ID) {
            return [
                'MERCHANDISE' => __('Merchandise'),
                'SAMPLE' => __('Sample'),
                'GIFT' => __('Gift'),
                'DOCUMENTS' => __('Documents'),
                'RETURN' => __('Return'),
                'OTHER' => __('Other')
            ];
        }

        return [];
    }

    /**
     * Parse zip from string to zip5-zip4
     *
     * @param string $zipString
     * @param bool $returnFull
     * @return string[]
     */
    protected function _parseZip($zipString, $returnFull = false)
    {
        $zip4 = '';
        $zip5 = '';
        $zip = [$zipString];
        if (preg_match('/[\\d\\w]{5}\\-[\\d\\w]{4}/', $zipString) != 0) {
            $zip = explode('-', $zipString);
        }
        for ($i = 0; $i < count($zip); ++$i) {
            if (strlen($zip[$i]) == 5) {
                $zip5 = $zip[$i];
            } elseif (strlen($zip[$i]) == 4) {
                $zip4 = $zip[$i];
            }
        }
        if (empty($zip5) && empty($zip4) && $returnFull) {
            $zip5 = $zipString;
        }

        return [$zip5, $zip4];
    }
}
