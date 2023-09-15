<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model;

use GuzzleHttp\Exception\GuzzleException;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Async\CallbackDeferred;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Measure\Length;
use Magento\Framework\Measure\Weight;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateMethodFactory;
use Magento\Sales\Model\Order\Shipment as OrderShipment;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\Result\ProxyDeferredFactory;
use Magento\Shipping\Model\Rate\ResultFactory as RateFactory;
use Magento\Shipping\Model\Shipment\Request as Shipment;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory as TrackStatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ups\Helper\Config;
use Magento\Ups\Model\UpsAuth;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * UPS shipping implementation.
 *
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
    public const CODE = 'ups';

    /**
     * Delivery Confirmation level based on origin/destination
     */
    public const DELIVERY_CONFIRMATION_SHIPMENT = 1;

    public const DELIVERY_CONFIRMATION_PACKAGE = 2;

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
     * @var float
     */
    protected $_baseCurrencyRate;

    /**
     * Test urls for shipment
     *
     * @var array
     */
    protected $_defaultUrls = [
        'ShipConfirm' => 'https://wwwcie.ups.com/api/shipments/v1/ship',
        'AuthUrl' => 'https://wwwcie.ups.com/security/v1/oauth/token',
    ];

    /**
     * Live urls for shipment
     *
     * @var array
     */
    protected $_liveUrls = [
        'ShipConfirm' => 'https://onlinetools.ups.com/api/shipments/v1/ship',
        'AuthUrl' => 'https://onlinetools.ups.com/security/v1/oauth/token',
    ];

    /**
     * Container types that could be customized for UPS carrier
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = ['CP', 'CSP'];

    /**
     * @var FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var UpsAuth
     */

    protected $upsAuth;

    /**
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = [
        'UserId',
        'Password',
    ];

    /**
     * @var AsyncClientInterface
     */
    private $asyncHttpClient;

    /**
     * @var ProxyDeferredFactory
     */
    private $deferredProxyFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RateErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param ElementFactory $xmlElFactory
     * @param RateFactory $rateFactory
     * @param RateMethodFactory $rateMethodFactory
     * @param TrackFactory $trackFactory
     * @param TrackErrorFactory $trackErrorFactory
     * @param TrackStatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param Data $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param FormatInterface $localeFormat
     * @param Config $configHelper
     * @param UpsAuth $upsAuth
     * @param ClientFactory $httpClientFactory
     * @param array $data
     * @param AsyncClientInterface|null $asyncHttpClient
     * @param ProxyDeferredFactory|null $proxyDeferredFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RateErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        RateFactory $rateFactory,
        RateMethodFactory $rateMethodFactory,
        TrackFactory $trackFactory,
        TrackErrorFactory $trackErrorFactory,
        TrackStatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        FormatInterface $localeFormat,
        Config $configHelper,
        UpsAuth $upsAuth,
        ClientFactory $httpClientFactory,
        array $data = [],
        ?AsyncClientInterface $asyncHttpClient = null,
        ?ProxyDeferredFactory $proxyDeferredFactory = null
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
        $this->_localeFormat = $localeFormat;
        $this->configHelper = $configHelper;
        $this->upsAuth = $upsAuth;
        $this->asyncHttpClient = $asyncHttpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
        $this->deferredProxyFactory = $proxyDeferredFactory
            ?? ObjectManager::getInstance()->get(ProxyDeferredFactory::class);
    }

    /**
     * Collect and get rates/errors
     *
     * @param RateRequest $request
     * @return Result|Error|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $this->setRequest($request);
        //To use the correct result in the callback.
        $this->_result = $result = $this->_getQuotes();

        return $this->deferredProxyFactory->create(
            [
                'deferred' => new CallbackDeferred(
                    function () use ($request, $result) {
                        $this->_result = $result;
                        $this->_updateFreeMethodQuote($request);
                        return $this->getResult();
                    }
                )
            ]
        );
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

        $rowRequest = new DataObject();

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
                OrderShipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }

        $rowRequest->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

        if ($request->getOrigRegionCode()) {
            $origRegionCode = $request->getOrigRegionCode();
        } else {
            $origRegionCode = $this->_scopeConfig->getValue(
                OrderShipment::XML_PATH_STORE_REGION_ID,
                ScopeInterface::SCOPE_STORE,
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
                    OrderShipment::XML_PATH_STORE_ZIP,
                    ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getOrigCity()) {
            $rowRequest->setOrigCity($request->getOrigCity());
        } else {
            $rowRequest->setOrigCity(
                $this->_scopeConfig->getValue(
                    OrderShipment::XML_PATH_STORE_CITY,
                    ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        $country = $this->_countryFactory->create()->load($destCountry);
        $rowRequest->setDestCountry($country->getData('iso2_code') ?: $destCountry);

        $rowRequest->setDestRegionCode($request->getDestRegionCode());

        if ($request->getDestPostcode()) {
            $rowRequest->setDestPostal($request->getDestPostcode());
        }

        $rowRequest->setOrigCountry(
            $this->getNormalizedCountryCode(
                $rowRequest->getOrigCountry(),
                $rowRequest->getOrigRegionCode(),
                $rowRequest->getOrigPostal()
            )
        );

        $rowRequest->setDestCountry(
            $this->getNormalizedCountryCode(
                $rowRequest->getDestCountry(),
                $rowRequest->getDestRegionCode(),
                $rowRequest->getDestPostal()
            )
        );

        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $rowRequest->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $rowRequest->setPackages(
            $this->createPackages(
                (float) $request->getPackageWeight(),
                (array) $request->getPackages()
            )
        );
        $rowRequest->setWeight($this->_getCorrectWeight($request->getPackageWeight()));
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
     * Return country code according to UPS
     *
     * @param string $countryCode
     * @param string $regionCode
     * @param string $postCode
     * @return string
     */
    private function getNormalizedCountryCode($countryCode, $regionCode, $postCode)
    {
        //for UPS, puerto rico state for US will assume as puerto rico country
        if ($countryCode == self::USA_COUNTRY_ID
            && ($postCode == '00912'
                || $regionCode == self::PUERTORICO_COUNTRY_ID)
        ) {
            $countryCode = self::PUERTORICO_COUNTRY_ID;
        }

        // For UPS, Guam state of the USA will be represented by Guam country
        if ($countryCode == self::USA_COUNTRY_ID && $regionCode == self::GUAM_REGION_CODE) {
            $countryCode = self::GUAM_COUNTRY_ID;
        }

        // For UPS, Las Palmas and Santa Cruz de Tenerife will be represented by Canary Islands country
        if ($countryCode === 'ES' &&
            ($regionCode === 'Las Palmas'
                || $regionCode === 'Santa Cruz de Tenerife')
        ) {
            $countryCode = 'IC';
        }

        return $countryCode;
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
        $r->setPackages(
            $this->createPackages((float)$r->getFreeMethodWeight(), [])
        );
        $r->setAction($this->configHelper->getCode('action', 'single'));
        $r->setProduct($freeMethod);
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
     * Get REST rates
     *
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getQuotes()
    {
        $url = $this->getConfigData('gateway_url');
        $accessToken = $this->setAPIAccessRequest();

        $rowRequest = $this->_rawRequest;
        if (self::USA_COUNTRY_ID == $rowRequest->getDestCountry()) {
            $destPostal = substr((string)$rowRequest->getDestPostal(), 0, 5);
        } else {
            $destPostal = $rowRequest->getDestPostal();
        }
        $params = [
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

        $shipperNumber = '';
        if ($this->getConfigFlag('negotiated_active') && ($shipperNumber = $this->getConfigData('shipper_number'))) {
            $shipperNumber = $this->getConfigData('shipper_number');
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

        $residentialAddressIndicator = '';
        if ($params['49_residential'] === '01') {
            $residentialAddressIndicator = $params['49_residential'];
        }

        $rateParams = [
            "RateRequest" => [
                "Request" => [
                    "TransactionReference" => [
                        "CustomerContext" => "Rating and Service"
                    ]
                ],
                "Shipment" => [
                    "Shipper" => [
                        "Name" => "UPS",
                        "ShipperNumber" => "{$shipperNumber}",
                        "Address" => [
                            "AddressLine" => [
                                "{$residentialAddressIndicator}",
                            ],
                            "City" => "{$shipperCity}",
                            "StateProvinceCode" => "{$shipperStateProvince}",
                            "PostalCode" => "{$shipperPostalCode}",
                            "CountryCode" => "{$shipperCountryCode}"
                        ]
                    ],
                    "ShipTo" => [
                        "Address" => [
                            "AddressLine" => ["{$params['49_residential']}"],
                            "StateProvinceCode" => "{$params['destRegionCode']}",
                            "PostalCode" => "{$params['19_destPostal']}",
                            "CountryCode" => "{$params['22_destCountry']}",
                            "ResidentialAddressIndicator" => "{$residentialAddressIndicator}"
                        ]
                    ],
                    "ShipFrom" => [
                        "Address" => [
                            "AddressLine" => [],
                            "StateProvinceCode" => "{$params['origRegionCode']}",
                            "PostalCode" => "{$params['15_origPostal']}",
                            "CountryCode" => "{$params['14_origCountry']}"
                        ]
                    ],
                ]
            ]
        ];

        if ($this->getConfigFlag('negotiated_active')) {
            $rateParams['RateRequest']['Shipment']['ShipmentRatingOptions']['TPFCNegotiatedRatesIndicator'] = "Y";
            $rateParams['RateRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = "Y";
        }
        if ($this->getConfigFlag('include_taxes')) {
            $rateParams['RateRequest']['Shipment']['TaxInformationIndicator'] = "Y";
        }

        if ($serviceCode !== null) {
            $rateParams['RateRequest']['Shipment']['Service']['code'] = $serviceCode;
            $rateParams['RateRequest']['Shipment']['Service']['Description'] = $serviceDescription;
        }

        foreach ($rowRequest->getPackages() as $package) {
            $rateParams['RateRequest']['Shipment']['Package'][] = [
                "PackagingType" => [
                    "Code" => "{$params['48_container']}",
                    "Description" => "Packaging"
                ],
                "Dimensions" => [
                    "UnitOfMeasurement" => [
                        "Code" => "IN",
                        "Description" => "Inches"
                    ],
                    "Length" => "5",
                    "Width" => "5",
                    "Height" => "5"
                ],
                "PackageWeight" => [
                    "UnitOfMeasurement" => [
                        "Code" => "{$rowRequest->getUnitMeasure()}"
                    ],
                    "Weight" => "{$this->_getCorrectWeight($package['weight'])}"
                ]
            ];
        }

        $ratePayload = json_encode($rateParams, JSON_PRETTY_PRINT);

        /** Rest API Payload */
        $version = "v1";
        $requestOption = $params['10_action'];
        $headers = [
            "Authorization" => "Bearer " . $accessToken,
            "Content-Type" => "application/json"
        ];
        $httpResponse = $this->asyncHttpClient->request(
            new Request($url.$version . "/" . $requestOption, Request::METHOD_POST, $headers, $ratePayload)
        );

        $debugData['request'] = $ratePayload;
        return $this->deferredProxyFactory->create(
            [
                'deferred' => new CallbackDeferred(
                    function () use ($httpResponse, $debugData) {
                        $responseResult = null;
                        $jsonResponse = '';
                        try {
                            $responseResult = $httpResponse->get();
                        } catch (HttpException $e) {
                            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                            $this->_logger->critical($e);
                        }
                        if ($responseResult) {
                            $jsonResponse = $responseResult->getStatusCode() >= 400 ? '' : $responseResult->getBody();
                        }
                        $debugData['result'] = $jsonResponse;
                        $this->_debug($debugData);

                        return $this->_parseRestResponse($jsonResponse);
                    }
                )
            ]
        );
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

        return $currencyMapping[$code] ?? $code;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $rateResponse
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function _parseRestResponse($rateResponse)
    {
        $costArr = [];
        $priceArr = [];
        if ($rateResponse !== null && strlen($rateResponse) > 0) {
            $rateResponseData = json_decode($rateResponse, true);
            if ($rateResponseData['RateResponse']['Response']['ResponseStatus']['Description'] === 'Success') {
                $arr = $rateResponseData['RateResponse']['RatedShipment'] ?? [];
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods') ?? '');

                $allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
                foreach ($arr as $shipElement) {
                    // Negotiated rates
                    $negotiatedArr = $shipElement['NegotiatedRateCharges'] ?? [] ;
                    $negotiatedActive = $this->getConfigFlag('negotiated_active')
                        && $this->getConfigData('shipper_number')
                        && !empty($negotiatedArr);

                    $this->processShippingRateForItem(
                        $shipElement,
                        $allowedMethods,
                        $allowedCurrencies,
                        $costArr,
                        $priceArr,
                        $negotiatedActive
                    );
                }
            } else {
                $errorTitle = $rateResponseData['RateResponse']['Response']['ResponseStatus']['Description'];
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
     * Processing rate for ship element
     *
     * @param array $shipElement
     * @param array $allowedMethods
     * @param array $allowedCurrencies
     * @param array $costArr
     * @param array $priceArr
     * @param bool $negotiatedActive
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function processShippingRateForItem(
        array $shipElement,
        array $allowedMethods,
        array $allowedCurrencies,
        array &$costArr,
        array &$priceArr,
        bool $negotiatedActive
    ): void {
        $code = $shipElement['Service']['Code'] ?? '';
        if (in_array($code, $allowedMethods)) {
            //The location of tax information is in a different place
            // depending on whether we are using negotiated rates or not
            if ($negotiatedActive) {
                $includeTaxesArr = $shipElement['NegotiatedRateCharges']['TotalChargesWithTaxes'] ?? [];
                $includeTaxesActive = $this->getConfigFlag('include_taxes') && !empty($includeTaxesArr);
                if ($includeTaxesActive) {
                    $cost = $shipElement['NegotiatedRateCharges']['TotalChargesWithTaxes']['MonetaryValue'];

                    $responseCurrencyCode = $this->mapCurrencyCode(
                        (string)$shipElement['NegotiatedRateCharges']['TotalChargesWithTaxes']['CurrencyCode']
                    );
                } else {
                    $cost = $shipElement['NegotiatedRateCharges']['TotalCharge']['MonetaryValue'];
                    $responseCurrencyCode = $this->mapCurrencyCode(
                        (string)$shipElement['NegotiatedRateCharges']['TotalCharge']['CurrencyCode']
                    );
                }
            } else {
                $includeTaxesArr = $shipElement['TotalChargesWithTaxes'] ?? [];
                $includeTaxesActive = $this->getConfigFlag('include_taxes') && !empty($includeTaxesArr);
                if ($includeTaxesActive) {
                    $cost = $shipElement['TotalChargesWithTaxes']['MonetaryValue'];
                    $responseCurrencyCode = $this->mapCurrencyCode(
                        (string)$shipElement['TotalChargesWithTaxes']['CurrencyCode']
                    );
                } else {
                    $cost = $shipElement['TotalCharges']['MonetaryValue'];
                    $responseCurrencyCode = $this->mapCurrencyCode(
                        (string)$shipElement['TotalCharges']['CurrencyCode']
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
        if ($handlingType == AbstractCarrier::HANDLING_TYPE_PERCENT) {
            return $cost + $cost * $this->_numBoxes * $handlingFee / 100;
        }

        return $cost + $this->_numBoxes * $handlingFee;
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
            return $cost + $cost * $handlingFee / 100;
        }

        return $cost + $handlingFee;
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
        $this->_getRestTracking($trackings);

        return $this->_result;
    }

    /**
     * To receive access token
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function setAPIAccessRequest()
    {
        $userId = $this->getConfigData('username');
        $userIdPass = $this->getConfigData('password');
        if ($this->getConfigData('is_account_live')) {
            $authUrl = $this->_liveUrls['AuthUrl'];
        } else {
            $authUrl = $this->_defaultUrls['AuthUrl'];
        }
        return $this->upsAuth->getAccessToken($userId, $userIdPass, $authUrl);
    }

    /**
     * Get REST tracking
     *
     * @param string[] $trackings
     * @return Result
     */
    protected function _getRestTracking($trackings)
    {
        $url = $this->getConfigData('tracking_url');
        $accessToken = $this->setAPIAccessRequest();

        /** @var HttpResponseDeferredInterface[] $trackingResponses */
        $trackingResponses = [];
        $tracking = '';
        $debugData = [];
        foreach ($trackings as $tracking) {
            /**
             * RequestOption==>'1' to request all activities
             */
            $queryParams = [
                "locale" => "en_US",
                "returnSignature" => "false"
            ];
            $trackParams = (object)[];
            $trackPayload = json_encode($trackParams);
            $transid = 'track'.uniqid();
            $headers = [
                "Authorization" => "Bearer " . $accessToken,
                "Content-Type" => "application/json",
                "transId" => $transid,
                "transactionSrc" => "testing"
            ];

            $debugData[$tracking] = ['request' => $trackPayload];
            $trackingResponses[$tracking] = $this->asyncHttpClient->request(
                new Request(
                    $url.'v1/details/'. $tracking . "?" . http_build_query($queryParams),
                    Request::METHOD_GET,
                    $headers,
                    $trackPayload
                )
            );
        }
        foreach ($trackingResponses as $tracking => $response) {
            $httpResponse = $response->get();
            $jsonResponse = $httpResponse->getStatusCode() >= 400 ? '' : $httpResponse->getBody();
            $debugData[$tracking]['result'] = $jsonResponse;
            $this->_debug($debugData);
            $this->_parseRestTrackingResponse($tracking, $jsonResponse);
        }

        return $this->_result;
    }

    /**
     * Parse REST tracking response
     *
     * @param string $trackingValue
     * @param string $jsonResponse
     * @return null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _parseRestTrackingResponse($trackingValue, $jsonResponse)
    {
        $errorTitle = 'For some reason we can\'t retrieve tracking info right now.';
        $resultArr = [];
        $packageProgress = [];

        if ($jsonResponse) {
            $responseData = json_decode($jsonResponse, true);

            if ($responseData['trackResponse']['shipment']) {
                $activityTags = $responseData['trackResponse']['shipment'][0]['package'][0]['activity'] ?? [];
                if ($activityTags) {
                    $index = 1;
                    foreach ($activityTags as $activityTag) {
                        $this->processActivityTagInfo($activityTag, $index, $resultArr, $packageProgress);
                    }
                    $resultArr['progressdetail'] = $packageProgress;
                }
            } else {
                $errorTitle = $responseData['errors']['message'];
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
     * Process tracking info from activity tag
     *
     * @param array $activityTag
     * @param int $index
     * @param array $resultArr
     * @param array $packageProgress
     */
    private function processActivityTagInfo(
        array $activityTag,
        int &$index,
        array &$resultArr,
        array &$packageProgress
    ) {
        $addressArr = [];
        if (isset($activityTag['location']['address']['city'])) {
            $addressArr[] = (string)$activityTag['location']['address']['city'];
        }
        if (isset($activityTag['location']['address']['stateProvince'])) {
            $addressArr[] = (string)$activityTag['location']['address']['stateProvince'];
        }
        if (isset($activityTag['location']['address']['countryCode'])) {
            $addressArr[] = (string)$activityTag['location']['address']['countryCode'];
        }
        $dateArr = [];
        $date = (string)$activityTag['date'];
        //YYYYMMDD
        $dateArr[] = substr($date, 0, 4);
        $dateArr[] = substr($date, 4, 2);
        $dateArr[] = substr($date, -2, 2);

        $timeArr = [];
        $time = (string)$activityTag['time'];
        //HHMMSS
        $timeArr[] = substr($time, 0, 2);
        $timeArr[] = substr($time, 2, 2);
        $timeArr[] = substr($time, -2, 2);

        if ($index === 1) {
            $resultArr['status'] = (string)$activityTag['status']['description'];
            $resultArr['deliverydate'] = implode('-', $dateArr);
            //YYYY-MM-DD
            $resultArr['deliverytime'] = implode(':', $timeArr);
            //HH:MM:SS
            if ($addressArr) {
                $resultArr['deliveryto'] = implode(', ', $addressArr);
            }
        } else {
            $tempArr = [];
            $tempArr['activity'] = (string)$activityTag['status']['description'];
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

        return $statuses ?: __('Empty response');
    }

    /**
     * Get allowed shipping methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowedMethods = explode(',', (string)$this->getConfigData('allowed_methods'));
        $origin = $this->getConfigData('origin_shipment');

        $availableByTypeMethods = $this->configHelper->getCode('originShipment', $origin);

        $methods = [];
        foreach ($availableByTypeMethods as $methodCode => $methodData) {
            if (in_array($methodCode, $allowedMethods)) {
                $methods[$methodCode] = $methodData->getText();
            }
        }

        return $methods;
    }

    /**
     * Form XML for shipment request
     *
     * @param DataObject $request
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formShipmentRequest(DataObject $request)
    {
        $packages = $request->getPackages();
        $shipmentItems = [];
        foreach ($packages as $package) {
            $shipmentItems[] = $package['items'];
        }
        $shipmentItems = array_merge([], ...$shipmentItems);

        /**  Shipment API Payload */

        $shipParams = [
            "ShipmentRequest" => [
                "Request" => [
                    "SubVersion" => "1801",
                    "RequestOption" => "nonvalidate",
                    "TransactionReference" => [
                        "CustomerContext" => "Shipment Request"
                    ]
                ],
                "Shipment" => [
                    "Description" => "{$this->generateShipmentDescription($shipmentItems)}",
                    "Shipper" => [],
                    "ShipTo" => [],
                    "ShipFrom" => [],
                    "PaymentInformation" => [],
                    "Service" => [],
                    "Package" => [],
                    "ShipmentServiceOptions" => []
                ],
                "LabelSpecification" => []
            ]
        ];

        if ($request->getIsReturn()) {
            $returnPart = &$shipParams['ShipmentRequest']['Shipment'];
            $returnPart['ReturnService']['Code'] = '9';
        }

        /** Shipment Details */
        if ($request->getIsReturn()) {
            $shipperData = &$shipParams['ShipmentRequest']['Shipment']['Shipper'];

            $shipperData['Name'] = $request->getRecipientContactCompanyName();
            $shipperData['AttentionName'] = $request->getRecipientContactPersonName();
            $shipperData['ShipperNumber'] = $this->getConfigData('shipper_number');
            $shipperData['Phone']['Number'] = $request->getRecipientContactPhoneNumber();

            $addressData = &$shipperData['Address'];
            $addressData['AddressLine'] =
                $request->getRecipientAddressStreet1().' '.$request->getRecipientAddressStreet2();
            $addressData['City'] = $request->getRecipientAddressCity();
            $addressData['CountryCode'] = $request->getRecipientAddressCountryCode();
            $addressData['PostalCode'] = $request->getRecipientAddressPostalCode();

            if ($request->getRecipientAddressStateOrProvinceCode()) {
                $addressData['StateProvinceCode'] = $request->getRecipientAddressStateOrProvinceCode();
            }
        } else {
            $shipperData = &$shipParams['ShipmentRequest']['Shipment']['Shipper'];

            $shipperData['Name'] = $request->getShipperContactCompanyName();
            $shipperData['AttentionName'] = $request->getShipperContactPersonName();
            $shipperData['ShipperNumber'] = $this->getConfigData('shipper_number');
            $shipperData['Phone']['Number'] = $request->getShipperContactPhoneNumber();

            $addressData = &$shipperData['Address'];
            $addressData['AddressLine'] = $request->getShipperAddressStreet1().' '.$request->getShipperAddressStreet2();
            $addressData['City'] = $request->getShipperAddressCity();
            $addressData['CountryCode'] = $request->getShipperAddressCountryCode();
            $addressData['PostalCode'] = $request->getShipperAddressPostalCode();

            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressData['StateProvinceCode'] = $request->getShipperAddressStateOrProvinceCode();
            }
        }

        $shipToData = &$shipParams['ShipmentRequest']['Shipment']['ShipTo'];
        $shipToData = [
            'Name'   => $request->getRecipientContactPersonName(),
            'AttentionName' => $request->getRecipientContactPersonName(),
            'Phone' => ['Number' => $request->getRecipientContactPhoneNumber()],
            'Address' => [
                'AddressLine' => $request->getRecipientAddressStreet1().' '.$request->getRecipientAddressStreet2(),
                'City' => $request->getRecipientAddressCity(),
                'CountryCode' => $request->getRecipientAddressCountryCode(),
                'PostalCode' => $request->getRecipientAddressPostalCode(),
            ],
        ];
        if ($request->getRecipientAddressStateOrProvinceCode()) {
            $shipToData['Address']['StateProvinceCode'] = $request->getRecipientAddressRegionCode();
        }
        if ($this->getConfigData('dest_type') == 'RES') {
            $shipToData['Address']['ResidentialAddress'] = '';
        }

        if ($request->getIsReturn()) {
            $shipFrom = &$shipParams['ShipmentRequest']['Shipment']['ShipFrom'];
            $shipFrom['Name'] = $request->getShipperContactPersonName();
            $shipFrom['AttentionName'] = $request->getShipperContactPersonName();
            $address = &$shipFrom['Address'];
            $address['AddressLine'] = $request->getShipperAddressStreet1().' '.$request->getShipperAddressStreet2();
            $address['City'] = $request->getShipperAddressCity();
            $address['CountryCode'] = $request->getShipperAddressCountryCode();
            $address['PostalCode'] = $request->getShipperAddressPostalCode();
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $address['StateProvinceCode'] = $request->getShipperAddressStateOrProvinceCode();
            }

            $shipToAddress = &$shipToData['Address'];
            $shipToAddress['AddressLine'] =
                $request->getShipperAddressStreet1().' '.$request->getShipperAddressStreet2();
            $shipToAddress['City'] = $request->getShipperAddressCity();
            $shipToAddress['CountryCode'] = $request->getShipperAddressCountryCode();
            $shipToAddress['PostalCode'] = $request->getShipperAddressPostalCode();
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $shipToAddress['StateProvinceCode'] = $request->getShipperAddressStateOrProvinceCode();
            }
            if ($this->getConfigData('dest_type') == 'RES') {
                $shipToAddress['ResidentialAddress'] = '';
            }
        }

        $shipParams['ShipmentRequest']['Shipment']['Service']['Code'] = $request->getShippingMethod();

        $packagePart = [];
        $customsTotal = 0;
        $packagingTypes = [];
        $deliveryConfirmationLevel = $this->_getDeliveryConfirmationLevel(
            $request->getRecipientAddressCountryCode()
        );
        foreach ($packages as $packageId => $package) {
            $packageItems = $package['items'];
            $packageParams = new DataObject($package['params']);
            $packagingType = $package['params']['container'];
            $packagingTypes[] = $packagingType;
            $height = $packageParams->getHeight();
            $width = $packageParams->getWidth();
            $length = $packageParams->getLength();
            $weight = $packageParams->getWeight();
            $weightUnits = $packageParams->getWeightUnits() == Weight::POUND ? 'LBS' : 'KGS';
            $dimensionsUnits = $packageParams->getDimensionUnits() == Length::INCH ? 'IN' : 'CM';
            $deliveryConfirmation = $packageParams->getDeliveryConfirmation();
            $customsTotal += $packageParams->getCustomsValue();

            $packagePart[$packageId] = &$shipParams['ShipmentRequest']['Shipment']['Package'];
            $packagePart[$packageId]['Description'] = $this->generateShipmentDescription($packageItems);
            //empirical
            $packagePart[$packageId]['Packaging']['Code'] = $packagingType;
            $packagePart[$packageId]['PackageWeight'] = [];
            $packageWeight = &$packagePart[$packageId]['PackageWeight'];
            $packageWeight['Weight'] = $weight;
            $packageWeight['UnitOfMeasurement']['Code'] = $weightUnits;
            // set dimensions
            if ($length || $width || $height) {
                $packagePart[$packageId]['Dimensions'] = [];
                $packageDimensions = &$packagePart[$packageId]['Dimensions'];
                $packageDimensions['UnitOfMeasurement']['Code'] = $dimensionsUnits;
                $packageDimensions['Length'] = $length;
                $packageDimensions['Width'] = $width;
                $packageDimensions['Height'] = $height;
            }
            // ups support reference number only for domestic service
            if ($this->_isUSCountry($request->getRecipientAddressCountryCode())
                && $this->_isUSCountry($request->getShipperAddressCountryCode())
            ) {
                if ($request->getReferenceData()) {
                    $referenceData = $request->getReferenceData() . $packageId;
                } else {
                    $referenceData = 'Order #' .
                        $request->getOrderShipment()->getOrder()->getIncrementId() .
                        ' P' .
                        $packageId;
                }
                $packagePart[$packageId]['ReferenceNumber'] = [];
                $referencePart = &$packagePart[$packageId]['ReferenceNumber'];
                $referencePart['Code'] = '02';
                $referencePart['Value'] = $referenceData;
            }
            if ($deliveryConfirmation && $deliveryConfirmationLevel === self::DELIVERY_CONFIRMATION_PACKAGE) {
                $packagePart[$packageId]['PackageServiceOptions']['DeliveryConfirmation']['DCISType'] =
                    $deliveryConfirmation;
            }
        }

        if (!empty($deliveryConfirmation) && $deliveryConfirmationLevel === self::DELIVERY_CONFIRMATION_SHIPMENT) {
            $shipParams['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['DeliveryConfirmation']['DCISType']
                = $deliveryConfirmation;
        }

        $shipParams['ShipmentRequest']['Shipment']['PaymentInformation']['ShipmentCharge']['Type'] = "01";
        $shipParams['ShipmentRequest']['Shipment']['PaymentInformation']['ShipmentCharge']['BillShipper']
        ['AccountNumber'] = $this->getConfigData('shipper_number');

        if (!in_array($this->configHelper->getCode('container', 'ULE'), $packagingTypes)
            && $request->getShipperAddressCountryCode() == self::USA_COUNTRY_ID
            && ($request->getRecipientAddressCountryCode() == 'CA'
                || $request->getRecipientAddressCountryCode() == 'PR')
        ) {
            $invoiceLineTotalPart = &$shipParams['ShipmentRequest']['Shipment']['InvoiceLineTotal'];
            $invoiceLineTotalPart['CurrencyCode'] = $request->getBaseCurrencyCode();
            $invoiceLineTotalPart['MonetaryValue'] = ceil($customsTotal);
        }

        /**  Label Details */

        $labelPart = &$shipParams['ShipmentRequest']['LabelSpecification'];
        $labelPart['LabelImageFormat']['Code'] = 'GIF';

        return json_encode($shipParams);
    }

    /**
     * Generates shipment description.
     *
     * @param array $items
     * @return string
     */
    private function generateShipmentDescription(array $items): string
    {
        $itemsDesc = [];
        $itemsShipment = $items;
        foreach ($itemsShipment as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);
            $itemsDesc[] = $item->getName();
        }

        return substr(implode(' ', $itemsDesc), 0, 35);
    }

    /**
     * Request quotes for given packages.
     *
     * @param DataObject $request
     * @return string[] Quote IDs.
     * @throws LocalizedException
     * @throws RuntimeException
     */
    private function requestQuotes(DataObject $request): array
    {
        $request->setShipperAddressCountryCode(
            $this->getNormalizedCountryCode(
                $request->getShipperAddressCountryCode(),
                $request->getShipperAddressStateOrProvinceCode(),
                $request->getShipperAddressPostalCode(),
            )
        );

        $request->setRecipientAddressCountryCode(
            $this->getNormalizedCountryCode(
                $request->getRecipientAddressCountryCode(),
                $request->getRecipientAddressStateOrProvinceCode(),
                $request->getRecipientAddressPostalCode(),
            )
        );

        /** @var HttpResponseDeferredInterface[] $quotesRequests */
        //Getting quotes
        $this->_prepareShipmentRequest($request);
        $rawJsonRequest = $this->_formShipmentRequest($request);
        $accessToken = $this->setAPIAccessRequest();
        $this->_debug(['request_quote' => $rawJsonRequest]);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '. $accessToken,
        ];
        $shippingRequests[] = $this->asyncHttpClient->request(
            new Request(
                $this->getShipConfirmUrl(),
                Request::METHOD_POST,
                $headers,
                $rawJsonRequest
            )
        );

        //Processing shipment requests
        /** @var DataObject[] $results */
        $results = [];
        foreach ($shippingRequests as $shippingRequest) {
            $httpResponse = $shippingRequest->get();
            if ($httpResponse->getStatusCode() >= 400) {
                throw new LocalizedException(__('Failed to send the package'));
            }
            try {
                /** @var Element $response */
                $response = $httpResponse->getBody();
                $this->_debug(['response_shipment' => $response]);
            } catch (Throwable $e) {
                throw new RuntimeException($e->getMessage());
            }
            if (isset($response->Error)) {
                throw new RuntimeException((string)$response->Error->ErrorDescription);
            }

            $responseShipment = json_decode($response, true);
            $result = new DataObject();
            $shippingLabelContent =
                (string)$responseShipment['ShipmentResponse']['ShipmentResults']['PackageResults']['ShippingLabel']
                ['GraphicImage'];
            $trackingNumber =
                (string)$responseShipment['ShipmentResponse']['ShipmentResults']['PackageResults']['TrackingNumber'];
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result->setLabelContent(base64_decode($shippingLabelContent));
            $result->setTrackingNumber($trackingNumber);
            $results[] = $result;
        }

        return $results;
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
     * @inheritDoc
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

        // phpcs:disable
        try {
            $labels = $this->requestQuotes($request);
        } catch (LocalizedException $exception) {
            $this->_logger->critical($exception);
            return new DataObject(['errors' => [$exception->getMessage()]]);
        } catch (RuntimeException $exception) {
            $this->_logger->critical($exception);
            return new DataObject(['errors' => __('Failed to send items')]);
        }
        // phpcs:enable

        return new DataObject(['info' => $labels]);
    }

    /**
     * @inheritDoc
     */
    public function returnOfShipment($request)
    {
        $request->setIsReturn(true);

        return $this->requestToShipment($request);
    }

    /**
     * Return container types of carrier
     *
     * @param DataObject|null $params
     * @return array|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getContainerTypes(DataObject $params = null)
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
        } elseif ($countryShipper == self::USA_COUNTRY_ID
            && $countryRecipient == self::PUERTORICO_COUNTRY_ID
            && in_array($method, ['01', '02', '03'])
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
     * @param DataObject|null $params
     * @return array|bool
     */
    public function getDeliveryConfirmationTypes(DataObject $params = null)
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
     *
     * Return null if delivery confirmation is not acceptable
     *
     * @param string|null $countyDestination
     * @return int|null
     */
    protected function _getDeliveryConfirmationLevel($countyDestination = null)
    {
        if ($countyDestination === null) {
            return null;
        }

        if ($countyDestination == self::USA_COUNTRY_ID) {
            return self::DELIVERY_CONFIRMATION_PACKAGE;
        }

        return self::DELIVERY_CONFIRMATION_SHIPMENT;
    }

    /**
     * Creates packages for rate request.
     *
     * @param float $totalWeight
     * @param array $packages
     * @return array
     */
    private function createPackages(float $totalWeight, array $packages): array
    {
        if (empty($packages)) {
            $dividedWeight = $this->getTotalNumOfBoxes($totalWeight);
            for ($i=0; $i < $this->_numBoxes; $i++) {
                $packages[$i]['weight'] = $this->_getCorrectWeight($dividedWeight);
            }
        }
        $this->_numBoxes = count($packages);

        return $packages;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        return ''; //This method has kept empty as not required.
    }
}
