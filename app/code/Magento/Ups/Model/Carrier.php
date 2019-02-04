<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Ups\Model;

use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Ups\Helper\Config;

/**
 * UPS shipping implementation
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'ups';

    /**
     * Delivery Confirmation level based on origin/destination
     */
    const DELIVERY_CONFIRMATION_SHIPMENT = 1;

    const DELIVERY_CONFIRMATION_PACKAGE = 2;

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     *
     * @var RateRequest
     */
    protected $_request;

    /**
     * Rate result data
     *
     * @var Result
     */
    protected $_result;

    /**
     * Base currency rate
     *
     * @var float
     */
    protected $_baseCurrencyRate;

    /**
     * Xml access request
     *
     * @var string
     */
    protected $_xmlAccessRequest;

    /**
     * Default cgi gateway url
     *
     * @var string
     */
    protected $_defaultCgiGatewayUrl = 'http://www.ups.com:80/using/services/rave/qcostcgi.cgi';

    /**
     * Test urls for shipment
     *
     * @var array
     */
    protected $_defaultUrls = [
        'ShipConfirm' => 'https://wwwcie.ups.com/ups.app/xml/ShipConfirm',
        'ShipAccept' => 'https://wwwcie.ups.com/ups.app/xml/ShipAccept',
    ];

    /**
     * Live urls for shipment
     *
     * @var array
     */
    protected $_liveUrls = [
        'ShipConfirm' => 'https://onlinetools.ups.com/ups.app/xml/ShipConfirm',
        'ShipAccept' => 'https://onlinetools.ups.com/ups.app/xml/ShipAccept',
    ];

    /**
     * Container types that could be customized for UPS carrier
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = ['CP', 'CSP'];

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @inheritdoc
     */
    protected $_debugReplacePrivateDataKeys = [
        'UserId', 'Password', 'AccessLicenseNumber'
    ];

    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

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
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param Config $configHelper
     * @param ClientFactory $httpClientFactory
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
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        Config $configHelper,
        ClientFactory $httpClientFactory,
        array $data = []
    ) {
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
        $this->httpClientFactory = $httpClientFactory;
        $this->_localeFormat = $localeFormat;
        $this->configHelper = $configHelper;
    }

    /**
     * Collect and get rates/errors
     *
     * @param RateRequest $request
     * @return  Result|Error|bool
     */
    public function collectRates(RateRequest $request)
    {
        $this->setRequest($request);
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
     * @param RateRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setRequest(RateRequest $request)
    {
        $this->_request = $request;

        $rowRequest = new \Magento\Framework\DataObject();

        if ($request->getLimitMethod()) {
            $rowRequest->setAction($this->configHelper->getCode('action', 'single'));
            $rowRequest->setProduct($request->getLimitMethod());
        } else {
            $rowRequest->setAction($this->configHelper->getCode('action', 'all'));
            $rowRequest->setProduct('GND' . $this->getConfigData('dest_type'));
        }

        if ($request->getUpsPickup()) {
            $pickup = $request->getUpsPickup();
        } else {
            $pickup = $this->getConfigData('pickup');
        }
        $rowRequest->setPickup($this->configHelper->getCode('pickup', $pickup));

        if ($request->getUpsContainer()) {
            $container = $request->getUpsContainer();
        } else {
            $container = $this->getConfigData('container');
        }
        $rowRequest->setContainer($this->configHelper->getCode('container', $container));

        if ($request->getUpsDestType()) {
            $destType = $request->getUpsDestType();
        } else {
            $destType = $this->getConfigData('dest_type');
        }
        $rowRequest->setDestType($this->configHelper->getCode('dest_type', $destType));

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }

        $rowRequest->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

        if ($request->getOrigRegionCode()) {
            $origRegionCode = $request->getOrigRegionCode();
        } else {
            $origRegionCode = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        if (is_numeric($origRegionCode)) {
            $origRegionCode = $this->_regionFactory->create()->load($origRegionCode)->getCode();
        }
        $rowRequest->setOrigRegionCode($origRegionCode);

        if ($request->getOrigPostcode()) {
            $rowRequest->setOrigPostal($request->getOrigPostcode());
        } else {
            $rowRequest->setOrigPostal(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getOrigCity()) {
            $rowRequest->setOrigCity($request->getOrigCity());
        } else {
            $rowRequest->setOrigCity(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
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

        //for UPS, puero rico state for US will assume as puerto rico country
        if ($destCountry == self::USA_COUNTRY_ID && ($request->getDestPostcode() == '00912' ||
                $request->getDestRegionCode() == self::PUERTORICO_COUNTRY_ID)
        ) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        // For UPS, Guam state of the USA will be represented by Guam country
        if ($destCountry == self::USA_COUNTRY_ID && $request->getDestRegionCode() == self::GUAM_REGION_CODE) {
            $destCountry = self::GUAM_COUNTRY_ID;
        }

        // For UPS, Las Palmas and Santa Cruz de Tenerife will be represented by Canary Islands country
        if (
            $destCountry == self::SPAIN_COUNTRY_ID &&
            ($request->getDestRegionCode() == self::LAS_PALMAS_REGION_ID || $request->getDestRegionCode() == self::SANTA_CRUZ_DE_TENERIFE_REGION_ID)
        ) {
            $destCountry = self::CANARY_ISLANDS_COUNTRY_ID;
        }

        $country = $this->_countryFactory->create()->load($destCountry);
        $rowRequest->setDestCountry($country->getData('iso2_code') ?: $destCountry);

        $rowRequest->setDestRegionCode($request->getDestRegionCode());

        if ($request->getDestPostcode()) {
            $rowRequest->setDestPostal($request->getDestPostcode());
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());

        $weight = $this->_getCorrectWeight($weight);

        $rowRequest->setWeight($weight);
        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $rowRequest->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $rowRequest->setValue($request->getPackageValue());
        $rowRequest->setValueWithDiscount($request->getPackageValueWithDiscount());

        if ($request->getUpsUnitMeasure()) {
            $unit = $request->getUpsUnitMeasure();
        } else {
            $unit = $this->getConfigData('unit_of_measure');
        }
        $rowRequest->setUnitMeasure($unit);
        $rowRequest->setIsReturn($request->getIsReturn());
        $rowRequest->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->_rawRequest = $rowRequest;

        return $this;
    }

    /**
     * Get correct weight
     *
     * Namely:
     * Checks the current weight to comply with the minimum weight standards set by the carrier.
     * Then strictly rounds the weight up until the first significant digit after the decimal point.
     *
     * @param float|int $weight
     * @return float
     */
    protected function _getCorrectWeight($weight)
    {
        $minWeight = $this->getConfigData('min_package_weight');

        if ($weight < $minWeight) {
            $weight = $minWeight;
        }

        //rounds a number to one significant figure
        $weight = ceil($weight * 10) / 10;

        return $weight;
    }

    /**
     * Get result of request
     *
     * @return Result
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Do remote request for  and handle errors
     *
     * @return Result|null
     */
    protected function _getQuotes()
    {
        switch ($this->getConfigData('type')) {
            case 'UPS':
                return $this->_getCgiQuotes();
            case 'UPS_XML':
                return $this->_getXmlQuotes();
            default:
                break;
        }

        return null;
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
        $weight = $this->_getCorrectWeight($weight);
        $r->setWeight($weight);
        $r->setAction($this->configHelper->getCode('action', 'single'));
        $r->setProduct($freeMethod);
    }

    /**
     * Get cgi rates
     *
     * @return Result
     */
    protected function _getCgiQuotes()
    {
        $rowRequest = $this->_rawRequest;
        if (self::USA_COUNTRY_ID == $rowRequest->getDestCountry()) {
            $destPostal = substr($rowRequest->getDestPostal(), 0, 5);
        } else {
            $destPostal = $rowRequest->getDestPostal();
        }

        $params = [
            'accept_UPS_license_agreement' => 'yes',
            '10_action' => $rowRequest->getAction(),
            '13_product' => $rowRequest->getProduct(),
            '14_origCountry' => $rowRequest->getOrigCountry(),
            '15_origPostal' => $rowRequest->getOrigPostal(),
            'origCity' => $rowRequest->getOrigCity(),
            '19_destPostal' => $destPostal,
            '22_destCountry' => $rowRequest->getDestCountry(),
            '23_weight' => $rowRequest->getWeight(),
            '47_rate_chart' => $rowRequest->getPickup(),
            '48_container' => $rowRequest->getContainer(),
            '49_residential' => $rowRequest->getDestType(),
            'weight_std' => strtolower($rowRequest->getUnitMeasure()),
        ];
        $params['47_rate_chart'] = $params['47_rate_chart']['label'];

        $responseBody = $this->_getCachedQuotes($params);
        if ($responseBody === null) {
            $debugData = ['request' => $params];
            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultCgiGatewayUrl;
                }
                $client = new \Zend_Http_Client();
                $client->setUri($url);
                $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                $client->setParameterGet($params);
                $response = $client->request();
                $responseBody = $response->getBody();

                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($params, $responseBody);
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $responseBody = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseCgiResponse($responseBody);
    }

    /**
     * Get shipment by code
     *
     * @param string $code
     * @param string $origin
     * @return array|bool
     */
    public function getShipmentByCode($code, $origin = null)
    {
        if ($origin === null) {
            $origin = $this->getConfigData('origin_shipment');
        }
        $arr = $this->configHelper->getCode('originShipment', $origin);
        if (isset($arr[$code])) {
            return $arr[$code];
        } else {
            return false;
        }
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param string $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _parseCgiResponse($response)
    {
        $costArr = [];
        $priceArr = [];
        if (strlen(trim($response)) > 0) {
            $rRows = explode("\n", $response);
            $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
            foreach ($rRows as $rRow) {
                $row = explode('%', $rRow);
                switch (substr($row[0], -1)) {
                    case 3:
                    case 4:
                        if (in_array($row[1], $allowedMethods)) {
                            $responsePrice = $this->_localeFormat->getNumber($row[8]);
                            $costArr[$row[1]] = $responsePrice;
                            $priceArr[$row[1]] = $this->getMethodPrice($responsePrice, $row[1]);
                        }
                        break;
                    case 5:
                        $errorTitle = $row[1];
                        $message = __(
                            'Sorry, something went wrong. Please try again or contact us and we\'ll try to help.'
                        );
                        $this->_logger->debug($message . ': ' . $errorTitle);
                        break;
                    case 6:
                        if (in_array($row[3], $allowedMethods)) {
                            $responsePrice = $this->_localeFormat->getNumber($row[10]);
                            $costArr[$row[3]] = $responsePrice;
                            $priceArr[$row[3]] = $this->getMethodPrice($responsePrice, $row[3]);
                        }
                        break;
                    default:
                        break;
                }
            }
            asort($priceArr);
        }

        $result = $this->_rateFactory->create();

        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $methodArray = $this->configHelper->getCode('method', $method);
                $rate->setMethodTitle($methodArray);
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Get xml rates
     *
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getXmlQuotes()
    {
        $url = $this->getConfigData('gateway_xml_url');

        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest;
        $debugData['accessRequest'] = $this->filterDebugData($xmlRequest);

        $rowRequest = $this->_rawRequest;
        if (self::USA_COUNTRY_ID == $rowRequest->getDestCountry()) {
            $destPostal = substr($rowRequest->getDestPostal(), 0, 5);
        } else {
            $destPostal = $rowRequest->getDestPostal();
        }
        $params = [
            'accept_UPS_license_agreement' => 'yes',
            '10_action' => $rowRequest->getAction(),
            '13_product' => $rowRequest->getProduct(),
            '14_origCountry' => $rowRequest->getOrigCountry(),
            '15_origPostal' => $rowRequest->getOrigPostal(),
            'origCity' => $rowRequest->getOrigCity(),
            'origRegionCode' => $rowRequest->getOrigRegionCode(),
            '19_destPostal' => $destPostal,
            '22_destCountry' => $rowRequest->getDestCountry(),
            'destRegionCode' => $rowRequest->getDestRegionCode(),
            '23_weight' => $rowRequest->getWeight(),
            '47_rate_chart' => $rowRequest->getPickup(),
            '48_container' => $rowRequest->getContainer(),
            '49_residential' => $rowRequest->getDestType(),
        ];

        if ($params['10_action'] == '4') {
            $params['10_action'] = 'Shop';
            $serviceCode = null;
        } else {
            $params['10_action'] = 'Rate';
            $serviceCode = $rowRequest->getProduct() ? $rowRequest->getProduct() : null;
        }
        $serviceDescription = $serviceCode ? $this->getShipmentByCode($serviceCode) : '';

        $xmlParams = <<<XMLRequest
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>{$params['10_action']}</RequestOption>
  </Request>
  <PickupType>
          <Code>{$params['47_rate_chart']['code']}</Code>
          <Description>{$params['47_rate_chart']['label']}</Description>
  </PickupType>

  <Shipment>
XMLRequest;

        if ($serviceCode !== null) {
            $xmlParams .= "<Service>" .
                "<Code>{$serviceCode}</Code>" .
                "<Description>{$serviceDescription}</Description>" .
                "</Service>";
        }

        $xmlParams .= <<<XMLRequest
      <Shipper>
XMLRequest;

        if ($this->getConfigFlag('negotiated_active') && ($shipperNumber = $this->getConfigData('shipper_number'))) {
            $xmlParams .= "<ShipperNumber>{$shipperNumber}</ShipperNumber>";
        }

        if ($rowRequest->getIsReturn()) {
            $shipperCity = '';
            $shipperPostalCode = $params['19_destPostal'];
            $shipperCountryCode = $params['22_destCountry'];
            $shipperStateProvince = $params['destRegionCode'];
        } else {
            $shipperCity = $params['origCity'];
            $shipperPostalCode = $params['15_origPostal'];
            $shipperCountryCode = $params['14_origCountry'];
            $shipperStateProvince = $params['origRegionCode'];
        }

        $xmlParams .= <<<XMLRequest
      <Address>
          <City>{$shipperCity}</City>
          <PostalCode>{$shipperPostalCode}</PostalCode>
          <CountryCode>{$shipperCountryCode}</CountryCode>
          <StateProvinceCode>{$shipperStateProvince}</StateProvinceCode>
      </Address>
    </Shipper>
    
    <ShipTo>
      <Address>
          <PostalCode>{$params['19_destPostal']}</PostalCode>
          <CountryCode>{$params['22_destCountry']}</CountryCode>
          <ResidentialAddress>{$params['49_residential']}</ResidentialAddress>
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

        if ($params['49_residential'] === '01') {
            $xmlParams .= "<ResidentialAddressIndicator>{$params['49_residential']}</ResidentialAddressIndicator>";
        }

        $xmlParams .= <<<XMLRequest
      </Address>
    </ShipTo>
    
    <ShipFrom>
      <Address>
          <PostalCode>{$params['15_origPostal']}</PostalCode>
          <CountryCode>{$params['14_origCountry']}</CountryCode>
          <StateProvinceCode>{$params['origRegionCode']}</StateProvinceCode>
      </Address>
    </ShipFrom>

    <Package>
      <PackagingType>
        <Code>{$params['48_container']}</Code>
      </PackagingType>
      <PackageWeight>
        <UnitOfMeasurement>
          <Code>{$rowRequest->getUnitMeasure()}</Code>
        </UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
    </Package>
XMLRequest;

        if ($this->getConfigFlag('negotiated_active')) {
            $xmlParams .= "<RateInformation><NegotiatedRatesIndicator/></RateInformation>";
        }
        if ($this->getConfigFlag('include_taxes')) {
            $xmlParams .= "<TaxInformationIndicator/>";
        }

        $xmlParams .= <<<XMLRequest
      </Shipment>
    </RatingServiceSelectionRequest>
XMLRequest;

        $xmlRequest .= $xmlParams;

        $xmlResponse = $this->_getCachedQuotes($xmlRequest);
        if ($xmlResponse === null) {
            $debugData['request'] = $xmlParams;
            try {
                $client = $this->httpClientFactory->create();
                $client->post($url, $xmlRequest);
                $xmlResponse = $client->getBody();
                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $xmlResponse = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseXmlResponse($xmlResponse);
    }

    /**
     * Get base currency rate
     *
     * @param string $code
     * @return float
     */
    protected function _getBaseCurrencyRate($code)
    {
        if (!$this->_baseCurrencyRate) {
            $this->_baseCurrencyRate = $this->_currencyFactory->create()->load(
                $code
            )->getAnyRate(
                $this->_request->getBaseCurrency()->getCode()
            );
        }

        return $this->_baseCurrencyRate;
    }

    /**
     * Map currency alias to currency code
     *
     * @param string $code
     * @return string
     */
    private function mapCurrencyCode($code)
    {
        $currencyMapping = [
            'RMB' => 'CNY',
            'CNH' => 'CNY'
        ];

        return isset($currencyMapping[$code]) ? $currencyMapping[$code] : $code;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $xmlResponse
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function _parseXmlResponse($xmlResponse)
    {
        $costArr = [];
        $priceArr = [];
        if (strlen(trim($xmlResponse)) > 0) {
            $xml = new \Magento\Framework\Simplexml\Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0];
            if ($success === 1) {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                // Negotiated rates
                $negotiatedArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates");
                $negotiatedActive = $this->getConfigFlag(
                        'negotiated_active'
                    ) && $this->getConfigData(
                        'shipper_number'
                    ) && !empty($negotiatedArr);

                $allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
                foreach ($arr as $shipElement) {
                    $code = (string)$shipElement->Service->Code;
                    if (in_array($code, $allowedMethods)) {
                        //The location of tax information is in a different place depending on whether we are using negotiated rates or not
                        if ($negotiatedActive) {
                            $includeTaxesArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates/NetSummaryCharges/TotalChargesWithTaxes");
                            $includeTaxesActive = $this->getConfigFlag(
                                    'include_taxes'
                                ) && !empty($includeTaxesArr);
                            if ($includeTaxesActive) {
                                $cost = $shipElement->NegotiatedRates->NetSummaryCharges->TotalChargesWithTaxes->MonetaryValue;
                                $responseCurrencyCode = $this->mapCurrencyCode(
                                    (string)$shipElement->NegotiatedRates->NetSummaryCharges->TotalChargesWithTaxes->CurrencyCode
                                );
                            }
                            else {
                                $cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;                            
                                $responseCurrencyCode = $this->mapCurrencyCode(
                                    (string)$shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->CurrencyCode
                                );
                            }
                        } else {
                            $includeTaxesArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/TotalChargesWithTaxes");
                            $includeTaxesActive = $this->getConfigFlag(
                                    'include_taxes'
                                ) && !empty($includeTaxesArr);                              
                            if ($includeTaxesActive) {
                                $cost = $shipElement->TotalChargesWithTaxes->MonetaryValue;
                                $responseCurrencyCode = $this->mapCurrencyCode(
                                    (string)$shipElement->TotalChargesWithTaxes->CurrencyCode
                                );
                            }
                            else {
                                $cost = $shipElement->TotalCharges->MonetaryValue;                            
                                $responseCurrencyCode = $this->mapCurrencyCode(
                                    (string)$shipElement->TotalCharges->CurrencyCode
                                );
                            }
                        }

                        //convert price with Origin country currency code to base currency code
                        $successConversion = true;
                        if ($responseCurrencyCode) {
                            if (in_array($responseCurrencyCode, $allowedCurrencies)) {
                                $cost = (double)$cost * $this->_getBaseCurrencyRate($responseCurrencyCode);
                            } else {
                                $errorTitle = __(
                                    'We can\'t convert a rate from "%1-%2".',
                                    $responseCurrencyCode,
                                    $this->_request->getPackageCurrency()->getCode()
                                );
                                $error = $this->_rateErrorFactory->create();
                                $error->setCarrier('ups');
                                $error->setCarrierTitle($this->getConfigData('title'));
                                $error->setErrorMessage($errorTitle);
                                $successConversion = false;
                            }
                        }

                        if ($successConversion) {
                            $costArr[$code] = $cost;
                            $priceArr[$code] = $this->getMethodPrice((float)$cost, $code);
                        }
                    }
                }
            } else {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier('ups');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            }
        }

        $result = $this->_rateFactory->create();

        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            if ($this->getConfigData('specificerrmsg') !== '') {
                $errorTitle = $this->getConfigData('specificerrmsg');
            }
            if (!isset($errorTitle)) {
                $errorTitle = __('Cannot retrieve shipping rates');
            }
            $error->setErrorMessage($errorTitle);
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $methodArr = $this->getShipmentByCode($method);
                $rate->setMethodTitle($methodArr);
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
     * @return Result
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        if ($this->getConfigData('type') == 'UPS') {
            $this->_getCgiTracking($trackings);
        } elseif ($this->getConfigData('type') == 'UPS_XML') {
            $this->setXMLAccessRequest();
            $this->_getXmlTracking($trackings);
        }

        return $this->_result;
    }

    /**
     * Set xml access request
     *
     * @return void
     */
    protected function setXMLAccessRequest()
    {
        $userId = $this->getConfigData('username');
        $userIdPass = $this->getConfigData('password');
        $accessKey = $this->getConfigData('access_license_number');

        $this->_xmlAccessRequest = <<<XMLAuth
<?xml version="1.0" ?>
<AccessRequest xml:lang="en-US">
  <AccessLicenseNumber>$accessKey</AccessLicenseNumber>
  <UserId>$userId</UserId>
  <Password>$userIdPass</Password>
</AccessRequest>
XMLAuth;
    }

    /**
     * Get cgi tracking
     *
     * @param string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\ResultFactory
     */
    protected function _getCgiTracking($trackings)
    {
        //ups no longer support tracking for data streaming version
        //so we can only reply the popup window to ups.
        $result = $this->_trackFactory->create();
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier('ups');
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl(
                "http://wwwapps.ups.com/WebTracking/processInputRequest?HTMLVersion=5.0&error_carried=true" .
                "&tracknums_displayed=5&TypeOfInquiryNumber=T&loc=en_US&InquiryNumber1={$tracking}" .
                "&AgreeToTermsAndConditions=yes"
            );
            $result->append($status);
        }

        $this->_result = $result;

        return $result;
    }

    /**
     * Get xml tracking
     *
     * @param string[] $trackings
     * @return Result
     */
    protected function _getXmlTracking($trackings)
    {
        $url = $this->getConfigData('tracking_xml_url');

        foreach ($trackings as $tracking) {

            /**
             * RequestOption==>'1' to request all activities
             */
            $xmlRequest = <<<XMLAuth
<?xml version="1.0" ?>
<TrackRequest xml:lang="en-US">
    <Request>
        <RequestAction>Track</RequestAction>
        <RequestOption>1</RequestOption>
    </Request>
    <TrackingNumber>$tracking</TrackingNumber>
    <IncludeFreight>01</IncludeFreight>
</TrackRequest>
XMLAuth;
            $debugData['request'] = parent::filterDebugData($this->_xmlAccessRequest) . $xmlRequest;
            try {
                $client = $this->httpClientFactory->create();
                $client->post($url, $this->_xmlAccessRequest . $xmlRequest);
                $xmlResponse = $client->getBody();
                $debugData['result'] = $xmlResponse;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $xmlResponse = '';
            }

            $this->_debug($debugData);
            $this->_parseXmlTrackingResponse($tracking, $xmlResponse);
        }

        return $this->_result;
    }

    /**
     * Parse xml tracking response
     *
     * @param string $trackingValue
     * @param string $xmlResponse
     * @return null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _parseXmlTrackingResponse($trackingValue, $xmlResponse)
    {
        $errorTitle = 'For some reason we can\'t retrieve tracking info right now.';
        $resultArr = [];
        $packageProgress = [];

        if ($xmlResponse) {
            $xml = new \Magento\Framework\Simplexml\Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//TrackResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0][0];

            if ($success === 1) {
                $arr = $xml->getXpath("//TrackResponse/Shipment/Service/Description/text()");
                $resultArr['service'] = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/PickupDate/text()");
                $resultArr['shippeddate'] = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/Weight/text()");
                $weight = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/UnitOfMeasurement/Code/text()");
                $unit = (string)$arr[0];

                $resultArr['weight'] = "{$weight} {$unit}";

                $activityTags = $xml->getXpath("//TrackResponse/Shipment/Package/Activity");
                if ($activityTags) {
                    $index = 1;
                    foreach ($activityTags as $activityTag) {
                        $addressArr = [];
                        if (isset($activityTag->ActivityLocation->Address->City)) {
                            $addressArr[] = (string)$activityTag->ActivityLocation->Address->City;
                        }
                        if (isset($activityTag->ActivityLocation->Address->StateProvinceCode)) {
                            $addressArr[] = (string)$activityTag->ActivityLocation->Address->StateProvinceCode;
                        }
                        if (isset($activityTag->ActivityLocation->Address->CountryCode)) {
                            $addressArr[] = (string)$activityTag->ActivityLocation->Address->CountryCode;
                        }
                        $dateArr = [];
                        $date = (string)$activityTag->Date;
                        //YYYYMMDD
                        $dateArr[] = substr($date, 0, 4);
                        $dateArr[] = substr($date, 4, 2);
                        $dateArr[] = substr($date, -2, 2);

                        $timeArr = [];
                        $time = (string)$activityTag->Time;
                        //HHMMSS
                        $timeArr[] = substr($time, 0, 2);
                        $timeArr[] = substr($time, 2, 2);
                        $timeArr[] = substr($time, -2, 2);

                        if ($index === 1) {
                            $resultArr['status'] = (string)$activityTag->Status->StatusType->Description;
                            $resultArr['deliverydate'] = implode('-', $dateArr);
                            //YYYY-MM-DD
                            $resultArr['deliverytime'] = implode(':', $timeArr);
                            //HH:MM:SS
                            $resultArr['deliverylocation'] = (string)$activityTag->ActivityLocation->Description;
                            $resultArr['signedby'] = (string)$activityTag->ActivityLocation->SignedForByName;
                            if ($addressArr) {
                                $resultArr['deliveryto'] = implode(', ', $addressArr);
                            }
                        } else {
                            $tempArr = [];
                            $tempArr['activity'] = (string)$activityTag->Status->StatusType->Description;
                            $tempArr['deliverydate'] = implode('-', $dateArr);
                            //YYYY-MM-DD
                            $tempArr['deliverytime'] = implode(':', $timeArr);
                            //HH:MM:SS
                            if ($addressArr) {
                                $tempArr['deliverylocation'] = implode(', ', $addressArr);
                            }
                            $packageProgress[] = $tempArr;
                        }
                        $index++;
                    }
                    $resultArr['progressdetail'] = $packageProgress;
                }
            } else {
                $arr = $xml->getXpath("//TrackResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
            }
        }

        if (!$this->_result) {
            $this->_result = $this->_trackFactory->create();
        }

        if ($resultArr) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier('ups');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingValue);
            $tracking->addData($resultArr);
            $this->_result->append($tracking);
        } else {
            $error = $this->_trackErrorFactory->create();
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingValue);
            $error->setErrorMessage($errorTitle);
            $this->_result->append($error);
        }

        return $this->_result;
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
            $trackings = $this->_result->getAllTrackings();
            if ($trackings) {
                foreach ($trackings as $tracking) {
                    $data = $tracking->getAllData();
                    if ($data) {
                        if (isset($data['status'])) {
                            $statuses .= __($data['status']);
                        } else {
                            $statuses .= __($data['error_message']);
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
        $isByCode = $this->getConfigData('type') == 'UPS_XML';
        foreach ($allowed as $code) {
            $arr[$code] = $isByCode ? $this->getShipmentByCode($code) : $this->configHelper->getCode('method', $code);
        }

        return $arr;
    }

    /**
     * Form XML for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $packageParams = $request->getPackageParams();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == \Zend_Measure_Weight::POUND ? 'LBS' : 'KGS';
        $dimensionsUnits = $packageParams->getDimensionUnits() == \Zend_Measure_Length::INCH ? 'IN' : 'CM';

        $itemsDesc = [];
        $itemsShipment = $request->getPackageItems();
        foreach ($itemsShipment as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);
            $itemsDesc[] = $item->getName();
        }

        $xmlRequest = $this->_xmlElFactory->create(
            ['data' => '<?xml version = "1.0" ?><ShipmentConfirmRequest xml:lang="en-US"/>']
        );
        $requestPart = $xmlRequest->addChild('Request');
        $requestPart->addChild('RequestAction', 'ShipConfirm');
        $requestPart->addChild('RequestOption', 'nonvalidate');

        $shipmentPart = $xmlRequest->addChild('Shipment');
        if ($request->getIsReturn()) {
            $returnPart = $shipmentPart->addChild('ReturnService');
            // UPS Print Return Label
            $returnPart->addChild('Code', '9');
        }
        $shipmentPart->addChild('Description', substr(implode(' ', $itemsDesc), 0, 35));
        //empirical

        $shipperPart = $shipmentPart->addChild('Shipper');
        if ($request->getIsReturn()) {
            $shipperPart->addChild('Name', $request->getRecipientContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getRecipientContactPersonName());
            $shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
            $shipperPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
            $addressPart->addChild('City', $request->getRecipientAddressCity());
            $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
            if ($request->getRecipientAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressStateOrProvinceCode());
            }
        } else {
            $shipperPart->addChild('Name', $request->getShipperContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
            $shipperPart->addChild('PhoneNumber', $request->getShipperContactPhoneNumber());

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
        }

        $shipToPart = $shipmentPart->addChild('ShipTo');
        $shipToPart->addChild('AttentionName', $request->getRecipientContactPersonName());
        $shipToPart->addChild(
            'CompanyName',
            $request->getRecipientContactCompanyName() ? $request->getRecipientContactCompanyName() : 'N/A'
        );
        $shipToPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

        $addressPart = $shipToPart->addChild('Address');
        $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet1());
        $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
        $addressPart->addChild('City', $request->getRecipientAddressCity());
        $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
        $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
        if ($request->getRecipientAddressStateOrProvinceCode()) {
            $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressRegionCode());
        }
        if ($this->getConfigData('dest_type') == 'RES') {
            $addressPart->addChild('ResidentialAddress');
        }

        if ($request->getIsReturn()) {
            $shipFromPart = $shipmentPart->addChild('ShipFrom');
            $shipFromPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipFromPart->addChild(
                'CompanyName',
                $request->getShipperContactCompanyName() ? $request
                    ->getShipperContactCompanyName() : $request
                    ->getShipperContactPersonName()
            );
            $shipFromAddress = $shipFromPart->addChild('Address');
            $shipFromAddress->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $shipFromAddress->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $shipFromAddress->addChild('City', $request->getShipperAddressCity());
            $shipFromAddress->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $shipFromAddress->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $shipFromAddress->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }

            $addressPart = $shipToPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
            if ($this->getConfigData('dest_type') == 'RES') {
                $addressPart->addChild('ResidentialAddress');
            }
        }

        $servicePart = $shipmentPart->addChild('Service');
        $servicePart->addChild('Code', $request->getShippingMethod());
        $packagePart = $shipmentPart->addChild('Package');
        $packagePart->addChild('Description', substr(implode(' ', $itemsDesc), 0, 35));
        //empirical
        $packagePart->addChild('PackagingType')->addChild('Code', $request->getPackagingType());
        $packageWeight = $packagePart->addChild('PackageWeight');
        $packageWeight->addChild('Weight', $request->getPackageWeight());
        $packageWeight->addChild('UnitOfMeasurement')->addChild('Code', $weightUnits);

        // set dimensions
        if ($length || $width || $height) {
            $packageDimensions = $packagePart->addChild('Dimensions');
            $packageDimensions->addChild('UnitOfMeasurement')->addChild('Code', $dimensionsUnits);
            $packageDimensions->addChild('Length', $length);
            $packageDimensions->addChild('Width', $width);
            $packageDimensions->addChild('Height', $height);
        }

        // ups support reference number only for domestic service
        if ($this->_isUSCountry(
                $request->getRecipientAddressCountryCode()
            ) && $this->_isUSCountry(
                $request->getShipperAddressCountryCode()
            )
        ) {
            if ($request->getReferenceData()) {
                $referenceData = $request->getReferenceData() . $request->getPackageId();
            } else {
                $referenceData = 'Order #' .
                    $request->getOrderShipment()->getOrder()->getIncrementId() .
                    ' P' .
                    $request->getPackageId();
            }
            $referencePart = $packagePart->addChild('ReferenceNumber');
            $referencePart->addChild('Code', '02');
            $referencePart->addChild('Value', $referenceData);
        }

        $deliveryConfirmation = $packageParams->getDeliveryConfirmation();
        if ($deliveryConfirmation) {
            /** @var $serviceOptionsNode Element */
            $serviceOptionsNode = null;
            switch ($this->_getDeliveryConfirmationLevel($request->getRecipientAddressCountryCode())) {
                case self::DELIVERY_CONFIRMATION_PACKAGE:
                    $serviceOptionsNode = $packagePart->addChild('PackageServiceOptions');
                    break;
                case self::DELIVERY_CONFIRMATION_SHIPMENT:
                    $serviceOptionsNode = $shipmentPart->addChild('ShipmentServiceOptions');
                    break;
                default:
                    break;
            }
            if (!is_null($serviceOptionsNode)) {
                $serviceOptionsNode->addChild(
                    'DeliveryConfirmation'
                )->addChild(
                    'DCISType',
                    $packageParams->getDeliveryConfirmation()
                );
            }
        }

        $shipmentPart->addChild('PaymentInformation')
            ->addChild('Prepaid')
            ->addChild('BillShipper')
            ->addChild('AccountNumber', $this->getConfigData('shipper_number'));

        if ($request->getPackagingType() != $this->configHelper->getCode('container', 'ULE') &&
            $request->getShipperAddressCountryCode() == self::USA_COUNTRY_ID &&
            ($request->getRecipientAddressCountryCode() == 'CA' ||
                $request->getRecipientAddressCountryCode() == 'PR')
        ) {
            $invoiceLineTotalPart = $shipmentPart->addChild('InvoiceLineTotal');
            $invoiceLineTotalPart->addChild('CurrencyCode', $request->getBaseCurrencyCode());
            $invoiceLineTotalPart->addChild('MonetaryValue', ceil($packageParams->getCustomsValue()));
        }

        $labelPart = $xmlRequest->addChild('LabelSpecification');
        $labelPart->addChild('LabelPrintMethod')->addChild('Code', 'GIF');
        $labelPart->addChild('LabelImageFormat')->addChild('Code', 'GIF');

        return $xmlRequest->asXml();
    }

    /**
     * Send and process shipment accept request
     *
     * @param Element $shipmentConfirmResponse
     * @return \Magento\Framework\DataObject
     */
    protected function _sendShipmentAcceptRequest(Element $shipmentConfirmResponse)
    {
        $xmlRequest = $this->_xmlElFactory->create(
            ['data' => '<?xml version = "1.0" ?><ShipmentAcceptRequest/>']
        );
        $request = $xmlRequest->addChild('Request');
        $request->addChild('RequestAction', 'ShipAccept');
        $xmlRequest->addChild('ShipmentDigest', $shipmentConfirmResponse->ShipmentDigest);
        $debugData = ['request' => parent::filterDebugData($this->_xmlAccessRequest) . $xmlRequest->asXML()];

        try {
            $client = $this->httpClientFactory->create();
            $client->post($this->getShipAcceptUrl(), $this->_xmlAccessRequest . $xmlRequest->asXML());
            $xmlResponse = $client->getBody();
            $debugData['result'] = $xmlResponse;
            $this->_setCachedQuotes($xmlRequest, $xmlResponse);
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $xmlResponse = '';
        }

        try {
            $response = $this->_xmlElFactory->create(['data' => $xmlResponse]);
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }

        $result = new \Magento\Framework\DataObject();
        if (isset($response->Error)) {
            $result->setErrors((string)$response->Error->ErrorDescription);
        } else {
            $shippingLabelContent = (string)$response->ShipmentResults->PackageResults->LabelImage->GraphicImage;
            $trackingNumber = (string)$response->ShipmentResults->PackageResults->TrackingNumber;

            $result->setShippingLabelContent(base64_decode($shippingLabelContent));
            $result->setTrackingNumber($trackingNumber);
        }

        $this->_debug($debugData);

        return $result;
    }

    /**
     * Get ship accept url
     *
     * @return string
     */
    public function getShipAcceptUrl()
    {
        if ($this->getConfigData('is_account_live')) {
            $url = $this->_liveUrls['ShipAccept'];
        } else {
            $url = $this->_defaultUrls['ShipAccept'];
        }

        return $url;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();
        $rawXmlRequest = $this->_formShipmentRequest($request);
        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest . $rawXmlRequest;
        $xmlResponse = $this->_getCachedQuotes($xmlRequest);

        if ($xmlResponse === null) {
            $debugData['request'] = parent::filterDebugData($this->_xmlAccessRequest) . $rawXmlRequest;
            $url = $this->getShipConfirmUrl();
            $client = $this->httpClientFactory->create();
            try {
                $client->post($url, $xmlRequest);
                $xmlResponse = $client->getBody();
                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            } catch (\Exception $e) {
                $debugData['result'] = ['code' => $e->getCode(), 'error' => $e->getMessage()];
            }
        }

        try {
            $response = $this->_xmlElFactory->create(['data' => $xmlResponse]);
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $result->setErrors($e->getMessage());
        }

        if (isset($response->Response->Error)
            && in_array(
                $response->Response->Error->ErrorSeverity,
                ['Hard', 'Transient']
            )
        ) {
            $result->setErrors((string)$response->Response->Error->ErrorDescription);
        }

        $this->_debug($debugData);

        if ($result->hasErrors() || empty($response)) {
            return $result;
        } else {
            return $this->_sendShipmentAcceptRequest($response);
        }
    }

    /**
     * Get ship confirm url
     *
     * @return string
     */
    public function getShipConfirmUrl()
    {
        $url = $this->getConfigData('url');
        if (!$url) {
            if ($this->getConfigData('is_account_live')) {
                $url = $this->_liveUrls['ShipConfirm'];

                return $url;
            } else {
                $url = $this->_defaultUrls['ShipConfirm'];

                return $url;
            }
        }

        return $url;
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        if ($params === null) {
            return $this->_getAllowedContainers($params);
        }
        $method = $params->getMethod();
        $countryShipper = $params->getCountryShipper();
        $countryRecipient = $params->getCountryRecipient();

        if ($countryShipper == self::USA_COUNTRY_ID && $countryRecipient == self::CANADA_COUNTRY_ID ||
            $countryShipper == self::CANADA_COUNTRY_ID && $countryRecipient == self::USA_COUNTRY_ID ||
            $countryShipper == self::MEXICO_COUNTRY_ID && $countryRecipient == self::USA_COUNTRY_ID && $method == '11'
        ) {
            $containerTypes = [];
            if ($method == '07' || $method == '08' || $method == '65') {
                // Worldwide Expedited
                if ($method != '08') {
                    $containerTypes = [
                        '01' => __('UPS Letter Envelope'),
                        '24' => __('UPS Worldwide 25 kilo'),
                        '25' => __('UPS Worldwide 10 kilo'),
                    ];
                }
                $containerTypes = $containerTypes + [
                        '03' => __('UPS Tube'),
                        '04' => __('PAK'),
                        '2a' => __('Small Express Box'),
                        '2b' => __('Medium Express Box'),
                        '2c' => __('Large Express Box'),
                    ];
            }

            return ['00' => __('Customer Packaging')] + $containerTypes;
        } elseif ($countryShipper == self::USA_COUNTRY_ID &&
            $countryRecipient == self::PUERTORICO_COUNTRY_ID &&
            ($method == '03' ||
                $method == '02' ||
                $method == '01')
        ) {
            // Container types should be the same as for domestic
            $params->setCountryRecipient(self::USA_COUNTRY_ID);
            $containerTypes = $this->_getAllowedContainers($params);
            $params->setCountryRecipient($countryRecipient);

            return $containerTypes;
        }

        return $this->_getAllowedContainers($params);
    }

    /**
     * Return all container types of carrier
     *
     * @return array|bool
     */
    public function getContainerTypesAll()
    {
        $codes = $this->configHelper->getCode('container');
        $descriptions = $this->configHelper->getCode('container_description');
        $result = [];
        foreach ($codes as $key => &$code) {
            $result[$code] = $descriptions[$key];
        }

        return $result;
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
     * @return array|bool
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\DataObject $params = null)
    {
        $countryRecipient = $params != null ? $params->getCountryRecipient() : null;
        $deliveryConfirmationTypes = [];
        switch ($this->_getDeliveryConfirmationLevel($countryRecipient)) {
            case self::DELIVERY_CONFIRMATION_PACKAGE:
                $deliveryConfirmationTypes = [
                    1 => __('Delivery Confirmation'),
                    2 => __('Signature Required'),
                    3 => __('Adult Signature Required'),
                ];
                break;
            case self::DELIVERY_CONFIRMATION_SHIPMENT:
                $deliveryConfirmationTypes = [1 => __('Signature Required'), 2 => __('Adult Signature Required')];
                break;
            default:
                break;
        }
        array_unshift($deliveryConfirmationTypes, __('Not Required'));

        return $deliveryConfirmationTypes;
    }

    /**
     * Get Container Types, that could be customized for UPS carrier
     *
     * @return array
     */
    public function getCustomizableContainerTypes()
    {
        $result = [];
        $containerTypes = $this->configHelper->getCode('container');
        foreach (parent::getCustomizableContainerTypes() as $containerType) {
            $result[$containerType] = $containerTypes[$containerType];
        }

        return $result;
    }

    /**
     * Get delivery confirmation level based on origin/destination
     * Return null if delivery confirmation is not acceptable
     *
     * @param string|null $countyDestination
     * @return int|null
     */
    protected function _getDeliveryConfirmationLevel($countyDestination = null)
    {
        if (is_null($countyDestination)) {
            return null;
        }

        if ($countyDestination == self::USA_COUNTRY_ID) {
            return self::DELIVERY_CONFIRMATION_PACKAGE;
        }

        return self::DELIVERY_CONFIRMATION_SHIPMENT;
    }
}
