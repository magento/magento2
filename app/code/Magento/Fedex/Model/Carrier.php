<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

namespace Magento\Fedex\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Measure\Length;
use Magento\Framework\Measure\Weight;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;

/**
 * Fedex shipping implementation
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    public const CODE = 'fedex';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    public const RATE_REQUEST_GENERAL = 'general';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    public const RATE_REQUEST_SMARTPOST = 'SMART_POST';

    /**
     * Oauth End point to get Access Token
     *
     * @var string
     */
    public const OAUTH_REQUEST_END_POINT = 'oauth/token';

    /**
     * REST end point of Tracking API
     *
     * @var string
     */
    public const TRACK_REQUEST_END_POINT = 'track/v1/trackingnumbers';

    /**
     * REST end point for Rate API
     *
     * @var string
     */
    public const RATE_REQUEST_END_POINT = 'rate/v1/rates/quotes';

    /**
     * REST end point to Create Shipment
     *
     * @var string
     */
    public const SHIPMENT_REQUEST_END_POINT = '/ship/v1/shipments';

    /**
     * REST end point to cancel Shipment
     *
     * @var string
     */
    public const SHIPMENT_CANCEL_END_POINT = '/ship/v1/shipments/cancel';

    /**
     * Authentication Grant Type for REST end point
     *
     * @var string
     */
    public const AUTHENTICATION_GRANT_TYPE = 'client_credentials';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Types of rates, order is important
     *
     * @var array
     */
    protected $_ratesOrder = [
        'RATED_ACCOUNT_PACKAGE',
        'PAYOR_ACCOUNT_PACKAGE',
        'RATED_ACCOUNT_SHIPMENT',
        'PAYOR_ACCOUNT_SHIPMENT',
        'RATED_LIST_PACKAGE',
        'PAYOR_LIST_PACKAGE',
        'RATED_LIST_SHIPMENT',
        'PAYOR_LIST_SHIPMENT',
    ];

    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result = null;

    /**
     * Container types that could be customized for FedEx carrier
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = ['YOUR_PACKAGING'];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = [
        'client_id', 'client_secret',
    ];

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var array
     */
    private $baseCurrencyRate;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var DecoderInterface
     */
    private $decoderInterface;

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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\HTTP\Client\CurlFactory $curlFactory
     * @param \Magento\Framework\Url\DecoderInterface $decoderInterface
     * @param array $data
     * @param Json|null $serializer
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        CurlFactory $curlFactory,
        DecoderInterface $decoderInterface,
        array $data = [],
        ?Json $serializer = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
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
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->curlFactory = $curlFactory;
        $this->decoderInterface = $decoderInterface;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $this->setRequest($request);
        $this->_getQuotes();
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
     */
    public function setRequest(RateRequest $request)
    {
        $this->_request = $request;

        $r = new \Magento\Framework\DataObject();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        if ($request->getFedexAccount()) {
            $account = $request->getFedexAccount();
        } else {
            $account = $this->getConfigData('account');
        }
        $r->setAccount($account);

        if ($request->getFedexPackaging()) {
            $packaging = $request->getFedexPackaging();
        } else {
            $packaging = $this->getConfigData('packaging');
        }
        $r->setPackaging($packaging);

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        $r->setOrigCountry($this->_countryFactory->create()->load($origCountry)->getData('iso2_code'));

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

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry($this->_countryFactory->create()->load($destCountry)->getData('iso2_code'));

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        if ($request->getDestCity()) {
            $r->setDestCity($request->getDestCity());
        }

        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setWeight($request->getPackageWeight());
        $r->setValue($request->getPackagePhysicalValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $r->setPackages($this->createPackages((float) $request->getPackageWeight(), (array) $request->getPackages()));

        $r->setMeterNumber($this->getConfigData('meter_number'));
        $r->setKey($this->getConfigData('key'));
        $r->setPassword($this->getConfigData('password'));

        $r->setIsReturn($request->getIsReturn());

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
        if (!$this->_result) {
            $this->_result = $this->_trackFactory->create();
        }
        return $this->_result;
    }

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     * @return array
     */
    protected function _formRateRequest($purpose): array
    {
        $r = $this->_rawRequest;
        $ratesRequest = [
            'accountNumber' => [
                'value' => $r->getAccount()
            ],
            'requestedShipment' => [
                'pickupType' => $this->getConfigData('pickup_type'),
                'packagingType' => $r->getPackaging(),
                'shipper' => [
                    'address' => ['postalCode' => $r->getOrigPostal(), 'countryCode' => $r->getOrigCountry()],
                ],
                'recipient' => [
                    'address' => [
                        'postalCode' => $r->getDestPostal(),
                        'countryCode' => $r->getDestCountry(),
                        'residential' => (bool)$this->getConfigData('residence_delivery'),
                    ],
                ],
                'customsClearanceDetail' => [
                    'dutiesPayment' => [
                        'payor' => [
                            'responsibleParty' => [
                                'accountNumber' => [
                                    'value' => $r->getAccount()
                                ],
                                'address' => [
                                    'countryCode' => $r->getOrigCountry()
                                ]
                            ]
                        ],
                        'paymentType' => 'SENDER',
                    ],
                    'commodities' => [
                        [
                            'customsValue' => ['amount' => $r->getValue(), 'currency' => $this->getCurrencyCode()]
                        ]
                    ]
                ],
                'rateRequestType' => ['LIST', 'ACCOUNT']
            ]
        ];

        foreach ($r->getPackages() as $packageNum => $package) {
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['subPackagingType'] =
                'PACKAGE';
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['groupPackageCount'] = 1;
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['weight']['value']
                = (double) $package['weight'];
            $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['weight']['units']
                = $this->getConfigData('unit_of_measure');
            if (isset($package['price'])) {
                $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['declaredValue']['amount']
                    = (double) $package['price'];
                $ratesRequest['requestedShipment']['requestedPackageLineItems'][$packageNum]['declaredValue']
                ['currency'] = $this->getCurrencyCode();
            }
        }

        $ratesRequest['requestedShipment']['totalPackageCount'] = count($r->getPackages());
        if ($r->getDestCity()) {
            $ratesRequest['requestedShipment']['recipient']['address']['city'] = $r->getDestCity();
        }

        if ($purpose == self::RATE_REQUEST_SMARTPOST) {
            $ratesRequest['requestedShipment']['serviceType'] = self::RATE_REQUEST_SMARTPOST;
            $ratesRequest['requestedShipment']['smartPostInfoDetail'] = [
                'indicia' => (double)$r->getWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                'hubId' => $this->getConfigData('smartpost_hubid'),
            ];
        }

        return $ratesRequest;
    }

    /**
     * Makes remote request to the carrier and returns a response
     *
     * @param string $purpose
     * @return mixed
     */
    protected function _doRatesRequest($purpose): mixed
    {
        $response = null;
        $accessToken = $this->_getAccessToken();
        if (empty($accessToken)) {
            return null;
        }

        $ratesRequest = $this->_formRateRequest($purpose);
        $requestString = $this->serializer->serialize($ratesRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = ['request' => $this->filterDebugData($ratesRequest)];

        if ($response === null) {
            $response = $this->sendRequest(self::RATE_REQUEST_END_POINT, $requestString, $accessToken);
            $this->_setCachedQuotes($requestString, $response);
        }
        $debugData['result'] = $response;
        $this->_debug($debugData);
        return $response;
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Result
     */
    protected function _getQuotes()
    {
        $this->_result = $this->_rateFactory->create();
        // make separate request for Smart Post method
        $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));
        if (in_array(self::RATE_REQUEST_SMARTPOST, $allowedMethods)) {
            $response = $this->_doRatesRequest(self::RATE_REQUEST_SMARTPOST);
            $preparedSmartpost = $this->_prepareRateResponse($response);
            if (!$preparedSmartpost->getError()) {
                $this->_result->append($preparedSmartpost);
            }
        }
        // make general request for all methods
        $response = $this->_doRatesRequest(self::RATE_REQUEST_GENERAL);
        $preparedGeneral = $this->_prepareRateResponse($response);
        if (!$preparedGeneral->getError()
            || $this->_result->getError() && $preparedGeneral->getError()
            || empty($this->_result->getAllRates())
        ) {
            $this->_result->append($preparedGeneral);
        }

        return $this->_result;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareRateResponse($response): Result
    {
        $costArr = [];
        $priceArr = [];
        $errorTitle = __('For some reason we can\'t retrieve tracking info right now.');

        if (is_array($response)) {
            if (!empty($response['errors'])) {
                if (is_array($response['errors'])) {
                    $notification = reset($response['errors']);
                    $errorTitle = (string)$notification['message'];
                } else {
                    $errorTitle = (string)$response['errors']['message'];
                }
            } elseif (isset($response['output']['rateReplyDetails'])) {
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
                if (is_array($response['output']['rateReplyDetails'])) {
                    foreach ($response['output']['rateReplyDetails'] as $rate) {
                        $serviceName = (string)$rate['serviceType'];
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = $this->_getRateAmountOriginBased($rate);
                            $costArr[$serviceName] = $amount;
                            $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                        }
                    }
                    asort($priceArr);
                }
            }
        }

        $result = $this->_rateFactory->create();
        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($this->getCode('method', $method));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
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
     * Get origin based amount form response of rate estimation
     *
     * @param \stdClass $rate
     * @return null|float
     */
    protected function _getRateAmountOriginBased($rate): null|float
    {
        $amount = null;
        $currencyCode = '';
        $rateTypeAmounts = [];

        if (is_array($rate)) {
            // The "RATED..." rates are expressed in the currency of the origin country
            foreach ($rate['ratedShipmentDetails'] as $ratedShipmentDetail) {
                $netAmount = (string)$ratedShipmentDetail['totalNetCharge'];
                $currencyCode = (string)$ratedShipmentDetail['shipmentRateDetail']['currency'];
                if (!empty($ratedShipmentDetail['ratedPackages'])) {
                    $rateType = (string)reset($ratedShipmentDetail['ratedPackages'])
                                            ['packageRateDetail']['rateType'];
                    $rateTypeAmounts[$rateType] = $netAmount;
                }
            }

            foreach ($this->_ratesOrder as $rateType) {
                if (!empty($rateTypeAmounts[$rateType])) {
                    $amount = $rateTypeAmounts[$rateType];
                    break;
                }
            }

            if ($amount === null && !empty($rate['ratedShipmentDetails'][0]['totalNetCharge'])) {
                $amount = (string)$rate['ratedShipmentDetails'][0]['totalNetCharge'];
            }

            $amount = (float)$amount * $this->getBaseCurrencyRate($currencyCode);
        }

        return $amount;
    }

    /**
     * Returns base currency rate.
     *
     * @param string $currencyCode
     * @return float
     * @throws LocalizedException
     */
    private function getBaseCurrencyRate(string $currencyCode): float
    {
        if (!isset($this->baseCurrencyRate[$currencyCode])) {
            $baseCurrencyCode = $this->_request->getBaseCurrency()->getCode();
            $rate = $this->_currencyFactory->create()
                ->load($currencyCode)
                ->getAnyRate($baseCurrencyCode);
            if ($rate === false) {
                $errorMessage = __(
                    'Can\'t convert a shipping cost from "%1-%2" for FedEx carrier.',
                    $currencyCode,
                    $baseCurrencyCode
                );
                $this->_logger->critical($errorMessage);
                throw new LocalizedException($errorMessage);
            }
            $this->baseCurrencyRate[$currencyCode] = (float)$rate;
        }

        return $this->baseCurrencyRate[$currencyCode];
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
        $r->setWeight($weight);
        $r->setPackages($this->createPackages((float)$r->getFreeMethodWeight(), []));
        $r->setService($freeMethod);
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return \Magento\Framework\Phrase|array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = ''): \Magento\Framework\Phrase|array|false
    {
        $codes = [
            'method' => [
                'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => __('Europe First Priority'),
                'FEDEX_1_DAY_FREIGHT' => __('1 Day Freight'),
                'FEDEX_2_DAY_FREIGHT' => __('2 Day Freight'),
                'FEDEX_2_DAY' => __('2 Day'),
                'FEDEX_2_DAY_AM' => __('2 Day AM'),
                'FEDEX_3_DAY_FREIGHT' => __('3 Day Freight'),
                'FEDEX_EXPRESS_SAVER' => __('Express Saver'),
                'FEDEX_GROUND' => __('Ground'),
                'FIRST_OVERNIGHT' => __('First Overnight'),
                'GROUND_HOME_DELIVERY' => __('Home Delivery'),
                'INTERNATIONAL_ECONOMY' => __('International Economy'),
                'INTERNATIONAL_ECONOMY_FREIGHT' => __('Intl Economy Freight'),
                'INTERNATIONAL_FIRST' => __('International First'),
                'INTERNATIONAL_GROUND' => __('International Ground'),
                'INTERNATIONAL_PRIORITY' => __('International Priority'),
                'INTERNATIONAL_PRIORITY_FREIGHT' => __('Intl Priority Freight'),
                'PRIORITY_OVERNIGHT' => __('Priority Overnight'),
                'SMART_POST' => __('Smart Post'),
                'STANDARD_OVERNIGHT' => __('Standard Overnight'),
                'FEDEX_FREIGHT' => __('Freight'),
                'FEDEX_NATIONAL_FREIGHT' => __('National Freight'),
            ],
            'dropoff' => [
                'REGULAR_PICKUP' => __('Regular Pickup'),
                'REQUEST_COURIER' => __('Request Courier'),
                'DROP_BOX' => __('Drop Box'),
                'BUSINESS_SERVICE_CENTER' => __('Business Service Center'),
                'STATION' => __('Station'),
            ],
            'packaging' => [
                'FEDEX_ENVELOPE' => __('FedEx Envelope'),
                'FEDEX_PAK' => __('FedEx Pak'),
                'FEDEX_BOX' => __('FedEx Box'),
                'FEDEX_TUBE' => __('FedEx Tube'),
                'FEDEX_10KG_BOX' => __('FedEx 10kg Box'),
                'FEDEX_25KG_BOX' => __('FedEx 25kg Box'),
                'YOUR_PACKAGING' => __('Your Packaging'),
            ],
            'containers_filter' => [
                [
                    'containers' => ['FEDEX_ENVELOPE', 'FEDEX_PAK'],
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                'FEDEX_EXPRESS_SAVER',
                                'FEDEX_2_DAY',
                                'FEDEX_2_DAY_AM',
                                'STANDARD_OVERNIGHT',
                                'PRIORITY_OVERNIGHT',
                                'FIRST_OVERNIGHT',
                            ],
                        ],
                        'from_us' => [
                            'method' => ['INTERNATIONAL_FIRST', 'INTERNATIONAL_ECONOMY', 'INTERNATIONAL_PRIORITY'],
                        ],
                    ],
                ],
                [
                    'containers' => ['FEDEX_BOX', 'FEDEX_TUBE'],
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                'FEDEX_2_DAY',
                                'FEDEX_2_DAY_AM',
                                'STANDARD_OVERNIGHT',
                                'PRIORITY_OVERNIGHT',
                                'FIRST_OVERNIGHT',
                                'FEDEX_FREIGHT',
                                'FEDEX_1_DAY_FREIGHT',
                                'FEDEX_2_DAY_FREIGHT',
                                'FEDEX_3_DAY_FREIGHT',
                                'FEDEX_NATIONAL_FREIGHT',
                            ],
                        ],
                        'from_us' => [
                            'method' => ['INTERNATIONAL_FIRST', 'INTERNATIONAL_ECONOMY', 'INTERNATIONAL_PRIORITY'],
                        ],
                    ],
                ],
                [
                    'containers' => ['FEDEX_10KG_BOX', 'FEDEX_25KG_BOX'],
                    'filters' => [
                        'within_us' => [],
                        'from_us' => ['method' => ['INTERNATIONAL_PRIORITY']],
                    ],
                ],
                [
                    'containers' => ['YOUR_PACKAGING'],
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                'FEDEX_GROUND',
                                'GROUND_HOME_DELIVERY',
                                'SMART_POST',
                                'FEDEX_EXPRESS_SAVER',
                                'FEDEX_2_DAY',
                                'FEDEX_2_DAY_AM',
                                'STANDARD_OVERNIGHT',
                                'PRIORITY_OVERNIGHT',
                                'FIRST_OVERNIGHT',
                                'FEDEX_FREIGHT',
                                'FEDEX_1_DAY_FREIGHT',
                                'FEDEX_2_DAY_FREIGHT',
                                'FEDEX_3_DAY_FREIGHT',
                                'FEDEX_NATIONAL_FREIGHT',
                            ],
                        ],
                        'from_us' => [
                            'method' => [
                                'INTERNATIONAL_FIRST',
                                'INTERNATIONAL_ECONOMY',
                                'INTERNATIONAL_PRIORITY',
                                'INTERNATIONAL_GROUND',
                                'FEDEX_FREIGHT',
                                'FEDEX_1_DAY_FREIGHT',
                                'FEDEX_2_DAY_FREIGHT',
                                'FEDEX_3_DAY_FREIGHT',
                                'FEDEX_NATIONAL_FREIGHT',
                                'INTERNATIONAL_ECONOMY_FREIGHT',
                                'INTERNATIONAL_PRIORITY_FREIGHT',
                            ],
                        ],
                    ],
                ],
            ],
            'delivery_confirmation_types' => [
                'NO_SIGNATURE_REQUIRED' => __('Not Required'),
                'ADULT' => __('Adult'),
                'DIRECT' => __('Direct'),
                'INDIRECT' => __('Indirect'),
            ],
            'unit_of_measure' => [
                'LB' => __('Pounds'),
                'KG' => __('Kilograms'),
            ],
            'pickup_type' => [
                'CONTACT_FEDEX_TO_SCHEDULE' => __('Contact Fedex to Schedule'),
                'DROPOFF_AT_FEDEX_LOCATION' => __('DropOff at Fedex Location'),
                'USE_SCHEDULED_PICKUP' => __('Use Scheduled Pickup'),
                'ON_CALL' => __('On Call'),
                'PACKAGE_RETURN_PROGRAM' => __('Package Return Program'),
                'REGULAR_STOP' => __('Regular Stop'),
                'TAG' => __('Tag'),
            ]
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Return FeDex currency ISO code by Magento Base Currency Code
     *
     * @return string 3-digit currency code
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        $codes = [
            'DOP' => 'RDD',
            'XCD' => 'ECD',
            'ARS' => 'ARN',
            'SGD' => 'SID',
            'KRW' => 'WON',
            'JMD' => 'JAD',
            'CHF' => 'SFR',
            'JPY' => 'JYE',
            'KWD' => 'KUD',
            'GBP' => 'UKL',
            'AED' => 'DHS',
            'MXN' => 'NMP',
            'UYU' => 'UYP',
            'CLP' => 'CHP',
            'TWD' => 'NTD',
        ];
        $currencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();

        return $codes[$currencyCode] ?? $currencyCode;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\Result|null
     */
    public function getTracking($trackings): \Magento\Shipping\Model\Tracking\Result|null
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        foreach ($trackings as $tracking) {
            $this->_getTrackingInformation($tracking);
        }

        return $this->_result;
    }

    /**
     * Get Url for REST API
     *
     * @param string|null $endpoint
     * @return string
     */
    protected function _getUrl($endpoint = null): string
    {
        $url = $this->getConfigFlag('sandbox_mode') ? $this->getConfigData('sandbox_webservices_url')
            : $this->getConfigData('production_webservices_url');

        return $endpoint ? $url  . $endpoint : $url;
    }
    /**
     * Get Access Token for Rest API
     *
     * @return string|null
     */
    protected function _getAccessToken(): string|null
    {
        $apiKey = $this->getConfigData('api_key') ?? null;
        $secretKey = $this->getConfigData('secret_key') ?? null;

        return $this->retrieveAccessToken($apiKey, $secretKey);
    }

    /**
     * Make the call to get the access token
     *
     * @param string|null $apiKey
     * @param string|null $secretKey
     * @return string|null
     */
    private function retrieveAccessToken(?string $apiKey, ?string $secretKey): string|null
    {
        if (!$apiKey || !$secretKey) {
            $this->_debug(__('Authentication keys are missing.'));
            return null;
        }

        $requestArray = [
            'grant_type' => self::AUTHENTICATION_GRANT_TYPE,
            'client_id' => $apiKey,
            'client_secret' => $secretKey
        ];

        $request = http_build_query($requestArray);
        $accessToken = null;
        $response = $this->sendRequest(self::OAUTH_REQUEST_END_POINT, $request);

        if (!empty($response['errors'])) {
            $debugData = ['request_type' => 'Access Token Request', 'result' => $response];
            $this->_debug($debugData);
        } elseif (!empty($response['access_token'])) {
            $accessToken = $response['access_token'];
        }

        return $accessToken;
    }

    /**
     * Get Access Token for Tracking Rest API
     *
     * @return string|null
     */
    private function getTrackingApiAccessToken(): string|null
    {
        $trackingApiKey = $this->getConfigData('tracking_api_key') ?? null;
        $trackingSecretKey = $this->getConfigData('tracking_api_secret_key') ?? null;

        return $this->retrieveAccessToken($trackingApiKey, $trackingSecretKey);
    }

    /**
     * Send Curl Request
     *
     * @param string $endpoint
     * @param string $request
     * @param string|null $accessToken
     * @return array|bool
     */
    protected function sendRequest($endpoint, $request, $accessToken = null): array|bool
    {
        if ($accessToken) {
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$accessToken,
                'X-locale' => 'en_US',

            ];
        } else {
            $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        }

        $curlClient = $this->curlFactory->create();
        $url = $this->_getUrl($endpoint);
        try {
            $curlClient->setHeaders($headers);
            if ($endpoint == self::SHIPMENT_CANCEL_END_POINT) {
                $curlClient->setOptions([CURLOPT_ENCODING => 'gzip,deflate,sdch', CURLOPT_CUSTOMREQUEST => 'PUT']);
            } else {
                $curlClient->setOptions([CURLOPT_ENCODING => 'gzip,deflate,sdch']);
            }
            $curlClient->post($url, $request);
            $response = $curlClient->getBody();
            $debugData = ['curl_response' => $response];
            $this->_debug($debugData);
            return $this->serializer->unserialize($response);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return false;
    }

    /**
     * Send request for tracking
     *
     * @param string $tracking
     * @return void
     */
    protected function _getTrackingInformation($tracking): void
    {
        if ($this->getConfigData('enabled_tracking_api')) {
            $accessToken = $this->getTrackingApiAccessToken();
        } else {
            $accessToken = $this->_getAccessToken();
        }

        if (!empty($accessToken)) {

            $trackRequest = [
                'includeDetailedScans' => true,
                'trackingInfo' => [
                    [
                        'trackingNumberInfo' => [
                            'trackingNumber'=> $tracking
                        ]
                    ]
                ]
            ];

            $requestString = $this->serializer->serialize($trackRequest);
            $response = $this->_getCachedQuotes($requestString);
            $debugData = ['request' => $trackRequest];

            if ($response === null) {
                $response = $this->sendRequest(self::TRACK_REQUEST_END_POINT, $requestString, $accessToken);
                $this->_setCachedQuotes($requestString, $response);
            }
            $debugData['result'] = $response;

            $this->_debug($debugData);
            $this->_parseTrackingResponse($tracking, $response);
        } else {
            $this->appendTrackingError(
                $tracking,
                __('Authorization Error. No Access Token found with given credentials.')
            );
            return;
        }
    }

    /**
     * Parse tracking response
     *
     * @param string $trackingValue
     * @param array $response
     * @return void
     */
    protected function _parseTrackingResponse($trackingValue, $response): void
    {
        if (!is_array($response) || empty($response['output'])) {
            $this->_debug($response);
            $this->appendTrackingError($trackingValue, __('Invalid response from carrier'));
            return;
        } elseif (empty(reset($response['output']['completeTrackResults'])['trackResults'])) {
            $this->_debug('No available tracking items');
            $this->appendTrackingError($trackingValue, __('No available tracking items'));
            return;
        }

        $trackInfo = reset($response['output']['completeTrackResults'])['trackResults'];

        $result = $this->getResult();
        $carrierTitle = $this->getConfigData('title');
        $counter = 0;
        foreach ($trackInfo as $item) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier(self::CODE);
            $tracking->setCarrierTitle($carrierTitle);
            $tracking->setTracking($trackingValue);
            $tracking->addData($this->processTrackingDetails($item));
            $result->append($tracking);
            $counter ++;
        }

        // no available tracking details
        if (!$counter) {
            $this->appendTrackingError(
                $trackingValue,
                __('For some reason we can\'t retrieve tracking info right now.')
            );
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
                        if (!empty($data['status'])) {
                            $statuses .= __($data['status']) . "\n<br/>";
                        } else {
                            $statuses .= __('Empty response') . "\n<br/>";
                        }
                    }
                }
            }
        }
        // phpstan:ignore
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
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }

    /**
     * Form array with appropriate structure for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request)
    {
        if ($request->getReferenceData()) {
            $referenceData = $request->getReferenceData() . $request->getPackageId();
        } else {
            $referenceData = 'Order #' .
                $request->getOrderShipment()->getOrder()->getIncrementId() .
                ' P' .
                $request->getPackageId();
        }
        $packageParams = $request->getPackageParams();
        $customsValue = $packageParams->getCustomsValue();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == Weight::POUND ? 'LB' : 'KG';
        $unitPrice = 0;
        $itemsQty = 0;
        $itemsDesc = [];
        $countriesOfManufacture = [];
        $productIds = [];
        $packageItems = $request->getPackageItems();
        /** @var Order $order */
        $order = $request->getOrderShipment()->getOrder();
        foreach ($packageItems as $orderItemId => $itemShipment) {
            if ($item = $order->getItemById($orderItemId)) {
                $unitPrice += $item->getPrice();
                $itemsQty += $itemShipment['qty'];

                $itemsDesc[] = $item->getName();
                $productIds[] = $item->getProductId();
            }
        }

        // get countries of manufacture
        $productCollection = $this->_productCollectionFactory->create()->addStoreFilter(
            $request->getStoreId()
        )->addFieldToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToSelect(
            'country_of_manufacture'
        );
        foreach ($productCollection as $product) {
            $countriesOfManufacture[] = $product->getCountryOfManufacture();
        }

        $paymentType = $this->getPaymentType($request);
        $optionType = $request->getShippingMethod() == self::RATE_REQUEST_SMARTPOST
            ? 'SERVICE_DEFAULT' : $packageParams->getDeliveryConfirmation();

        $requestClient = [
            'requestedShipment' => [
                'shipDatestamp' => date('Y-m-d'),
                'pickupType' => $this->getConfigData('pickup_type'),
                'serviceType' => $request->getShippingMethod(),
                'packagingType' => $request->getPackagingType(),
                'shipper' => [
                    'contact' => [
                        'personName' => $request->getShipperContactPersonName(),
                        'companyName' => $request->getShipperContactCompanyName(),
                        'phoneNumber' => $request->getShipperContactPhoneNumber(),
                    ],
                    'address' => [
                        'streetLines' => [$request->getShipperAddressStreet()],
                        'city' => $request->getShipperAddressCity(),
                        'stateOrProvinceCode' => $request->getShipperAddressStateOrProvinceCode(),
                        'postalCode' => $request->getShipperAddressPostalCode(),
                        'countryCode' => $request->getShipperAddressCountryCode(),
                    ]
                ],
                'recipients' => [
                    [
                        'contact' => [
                            'personName' => $request->getRecipientContactPersonName(),
                            'companyName' => $request->getRecipientContactCompanyName(),
                            'phoneNumber' => $request->getRecipientContactPhoneNumber()
                        ],
                        'address' => [
                            'streetLines' => [$request->getRecipientAddressStreet()],
                            'city' => $request->getRecipientAddressCity(),
                            'stateOrProvinceCode' => $request->getRecipientAddressStateOrProvinceCode(),
                            'postalCode' => $request->getRecipientAddressPostalCode(),
                            'countryCode' => $request->getRecipientAddressCountryCode(),
                            'residential' => (bool)$this->getConfigData('residence_delivery'),
                        ]
                    ],
                ],
                'shippingChargesPayment' => [
                    'paymentType' => $paymentType,
                    'payor' => [
                        'responsibleParty' => [
                            'accountNumber' => ['value' => $this->getConfigData('account')]
                        ],
                    ],
                ],
                'labelSpecification' => [
                    'labelFormatType' => 'COMMON2D',
                    'imageType' => 'PNG',
                    'labelStockType' => 'PAPER_85X11_TOP_HALF_LABEL',
                ],
                'rateRequestType' => ['ACCOUNT'],
                'totalPackageCount' => 1
            ],
            'labelResponseOptions' => 'LABEL',
            'accountNumber' => ['value' => $this->getConfigData('account')]
        ];

        // for international shipping
        if ($request->getShipperAddressCountryCode() != $request->getRecipientAddressCountryCode()) {
            $requestClient['requestedShipment']['customsClearanceDetail'] = [
                'totalCustomsValue' => ['currency' => $request->getBaseCurrencyCode(), 'amount' => $customsValue],
                'dutiesPayment' => [
                    'paymentType' => $paymentType,
                    'payor' => [
                        'responsibleParty' => [
                            'accountNumber' => ['value' => $this->getConfigData('account')],
                            'address' => ['countryCode' => $this->_scopeConfig->getValue(
                                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $request->getStoreId()
                            )],
                        ],
                    ],
                ],
                'commodities' => [
                    [
                        'weight' => ['units' => $weightUnits, 'value' => $request->getPackageWeight()],
                        'numberOfPieces' => 1,
                        'countryOfManufacture' => implode(',', array_unique($countriesOfManufacture)),
                        'description' => implode(', ', $itemsDesc),
                        'quantity' => ceil($itemsQty),
                        'quantityUnits' => 'pcs',
                        'unitPrice' => ['currency' => $request->getBaseCurrencyCode(), 'amount' => $unitPrice],
                        'customsValue' => ['currency' => $request->getBaseCurrencyCode(), 'amount' => $customsValue],
                    ]
                ]
            ];
        }

        if ($request->getMasterTrackingId()) {
            $requestClient['requestedShipment']['masterTrackingId']['trackingNumber'] = $request->getMasterTrackingId();
        }

        if ($request->getShippingMethod() == self::RATE_REQUEST_SMARTPOST) {
            $requestClient['requestedShipment']['smartPostInfoDetail'] = [
                'indicia' => (double)$request->getPackageWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                'hubId' => $this->getConfigData('smartpost_hubid'),
            ];
        }

        $requestedPackageLineItems = [
            'sequenceNumber' => '1',
            'weight' => ['units' => $weightUnits, 'value' => $request->getPackageWeight()],
            'customerReferences' => [
                [
                    'customerReferenceType' => 'CUSTOMER_REFERENCE',
                    'value' => $referenceData,
                ]
            ],
            'packageSpecialServices' => [
                'specialServiceTypes' => ['SIGNATURE_OPTION'],
                'signatureOptionType' => $optionType

            ]
        ];

        // set dimensions
        if ($length || $width || $height) {
            $requestedPackageLineItems['dimensions'] = [
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'units' => $packageParams->getDimensionUnits() == Length::INCH ? 'IN' : 'CM',
            ];
        }

        $requestClient['requestedShipment']['requestedPackageLineItems'] = [$requestedPackageLineItems];

        return $requestClient;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request): \Magento\Framework\DataObject
    {
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();
        $accessToken = $this->_getAccessToken();
        if (empty($accessToken)) {
            return $result->setErrors(__('Authorization Error. No Access Token found with given credentials.'));
        }

        $requestClient = $this->_formShipmentRequest($request);
        $requestString = $this->serializer->serialize($requestClient);

        $debugData = ['request' => $this->filterDebugData($requestClient)];

        $response = $this->sendRequest(self::SHIPMENT_REQUEST_END_POINT, $requestString, $accessToken);

        $debugData['result'] = $response;

        if (!empty($response['output']['transactionShipments'])) {
            $shippingLabelContent = $this->getPackagingLabel(
                reset($response['output']['transactionShipments'])['pieceResponses']
            );

            $trackingNumber = $this->getTrackingNumber(
                reset($response['output']['transactionShipments'])['pieceResponses']
            );
                    $result->setShippingLabelContent($this->decoderInterface->decode($shippingLabelContent));
                    $result->setTrackingNumber($trackingNumber);
        } else {
            $debugData['result'] = ['error' => '', 'code' => '', 'message' => $response];
            if (is_array($response['errors'])) {
                foreach ($response['errors'] as $notification) {
                    $debugData['result']['code'] .= $notification['code'] . '; ';
                    $debugData['result']['error'] .= $notification['message'] . '; ';
                }
            } else {
                $debugData['result']['code'] = $response['errors']['code'] . ' ';
                $debugData['result']['error'] = $response['errors']['message'] . ' ';
            }

            $result->setErrors($debugData['result']['error'] . $debugData['result']['code']);
        }

        $this->_debug($debugData);
        $result->setGatewayResponse($response);
        return $result;
    }

    /**
     * Return Tracking Number
     *
     * @param array $pieceResponses
     * @return string
     */
    private function getTrackingNumber($pieceResponses): string
    {
        return reset($pieceResponses)['trackingNumber'];
    }

    /**
     * Return Packaging Label
     *
     * @param array|object $pieceResponses
     * @return string
     */
    private function getPackagingLabel($pieceResponses): string
    {
        return reset(reset($pieceResponses)['packageDocuments'])['encodedLabel'];
    }

    /**
     * For multi package shipments. Delete requested shipments if the current shipment request is failed
     *
     * @param array $data
     *
     * @return bool
     */
    public function rollBack($data): bool
    {
        $accessToken = $this->_getAccessToken();
        if (empty($accessToken)) {
            $this->_debug(__('Authorization Error. No Access Token found with given credentials.'));
            return false;
        }

        $requestData['accountNumber'] = ['value' => $this->getConfigData('account')];
        $requestData['deletionControl'] = 'DELETE_ALL_PACKAGES';

        foreach ($data as &$item) {
            $requestData['trackingNumber'] = $item['tracking_number'];
            $requestString = $this->serializer->serialize($requestData);

            $debugData = ['request' => $requestData];
            $response = $this->sendRequest(self::SHIPMENT_CANCEL_END_POINT, $requestString, $accessToken);
            $debugData['result'] = $response;

            $this->_debug($debugData);
        }
        return true;
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     *
     * @return array|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getContainerTypes(?\Magento\Framework\DataObject $params = null)
    {
        if ($params == null) {
            return $this->_getAllowedContainers($params);
        }
        $method = $params->getMethod();
        $countryShipper = $params->getCountryShipper();
        $countryRecipient = $params->getCountryRecipient();

        if (($countryShipper == self::USA_COUNTRY_ID && $countryRecipient == self::CANADA_COUNTRY_ID ||
            $countryShipper == self::CANADA_COUNTRY_ID &&
            $countryRecipient == self::USA_COUNTRY_ID) &&
            $method == 'FEDEX_GROUND'
        ) {
            return ['YOUR_PACKAGING' => __('Your Packaging')];
        } else {
            if ($method == 'INTERNATIONAL_ECONOMY' || $method == 'INTERNATIONAL_FIRST') {
                $allTypes = $this->getContainerTypesAll();
                $exclude = ['FEDEX_10KG_BOX' => '', 'FEDEX_25KG_BOX' => ''];

                return array_diff_key($allTypes, $exclude);
            } else {
                if ($method == 'EUROPE_FIRST_INTERNATIONAL_PRIORITY') {
                    $allTypes = $this->getContainerTypesAll();
                    $exclude = ['FEDEX_BOX' => '', 'FEDEX_TUBE' => ''];

                    return array_diff_key($allTypes, $exclude);
                } else {
                    if ($countryShipper == self::CANADA_COUNTRY_ID && $countryRecipient == self::CANADA_COUNTRY_ID) {
                        // hack for Canada domestic. Apply the same filter rules as for US domestic
                        $params->setCountryShipper(self::USA_COUNTRY_ID);
                        $params->setCountryRecipient(self::USA_COUNTRY_ID);
                    }
                }
            }
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
        return $this->getCode('packaging');
    }

    /**
     * Return structured data of containers witch related with shipping methods
     *
     * @return array|bool
     */
    public function getContainerTypesFilter()
    {
        return $this->getCode('containers_filter');
    }

    /**
     * Return delivery confirmation types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDeliveryConfirmationTypes(?\Magento\Framework\DataObject $params = null)
    {
        return $this->getCode('delivery_confirmation_types');
    }

    /**
     * Recursive replace sensitive fields in debug data by the mask
     *
     * @param string $data
     * @return string
     */
    protected function filterDebugData($data)
    {
        foreach (array_keys($data) as $key) {
            if (is_array($data[$key])) {
                $data[$key] = $this->filterDebugData($data[$key]);
            } elseif (in_array($key, $this->_debugReplacePrivateDataKeys)) {
                $data[$key] = self::DEBUG_KEYS_MASK;
            }
        }
        return $data;
    }

    /**
     * Parse track details response from Fedex
     *
     * @param array $trackInfo
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function processTrackingDetails($trackInfo): array
    {
        $result = [
            'shippeddate' => null,
            'deliverydate' => null,
            'deliverytime' => null,
            'deliverylocation' => null,
            'weight' => null,
            'progressdetail' => [],
        ];

        if (!empty($trackInfo['dateAndTimes']) && is_array($trackInfo['dateAndTimes'])) {
            $datetime = null;
            foreach ($trackInfo['dateAndTimes'] as $dateAndTimeInfo) {
                if (!empty($dateAndTimeInfo['type']) && $dateAndTimeInfo['type'] == 'SHIP') {
                    $datetime = $this->parseDate($dateAndTimeInfo['dateTime']);
                    break;
                }
            }

            if ($datetime) {
                $result['shippeddate'] = gmdate('Y-m-d', $datetime->getTimestamp());
            }
        }

        $result['signedby'] = !empty($trackInfo['deliveryDetails']['receivedByName']) ?
            (string) $trackInfo['deliveryDetails']['receivedByName'] :
            null;

        $result['status'] = (!empty($trackInfo['latestStatusDetail']) &&
                                    !empty($trackInfo['latestStatusDetail']['description'])) ?
            (string) $trackInfo['latestStatusDetail']['description'] :
            null;
        $result['service'] = (!empty($trackInfo['serviceDetail']) &&
                                    !empty($trackInfo['serviceDetail']['description'])) ?
            (string) $trackInfo['serviceDetail']['description'] :
            null;

        $datetime = $this->getDeliveryDateTime($trackInfo);
        if ($datetime) {
            $result['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
            $result['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
        }

        $address = null;
        if (!empty($trackInfo['deliveryDetails']['estimatedDeliveryAddress'])) {
            $address = $trackInfo['deliveryDetails']['estimatedDeliveryAddress'];
        } elseif (!empty($trackInfo['deliveryDetails']['actualDeliveryAddress'])) {
            $address = $trackInfo['deliveryDetails']['actualDeliveryAddress'];
        }

        if (!empty($address)) {
            $result['deliverylocation'] = $this->getDeliveryAddress($address);
        }

        if (!empty($trackInfo['packageDetails']['weightAndDimensions']['weight'])) {
            $weightUnit = $this->getConfigData('unit_of_measure') ?? 'LB';
            $weightValue = null;
            foreach ($trackInfo['packageDetails']['weightAndDimensions']['weight'] as $weightInfo) {
                if ($weightInfo['unit'] == $weightUnit) {
                    $weightValue = $weightInfo['value'];
                    break;
                }
            }

            $result['weight'] = sprintf(
                '%s %s',
                (string) $weightValue,
                (string) $weightUnit
            );
        }

        if (!empty($trackInfo['scanEvents'])) {
            $events = $trackInfo['scanEvents'];
            if (is_object($trackInfo['scanEvents'])) {
                $events = [$trackInfo['scanEvents']];
            }
            $result['progressdetail'] = $this->processTrackDetailsEvents($events);
        }

        return $result;
    }

    /**
     * Parse delivery datetime from tracking details
     *
     * @param array $trackInfo
     * @return \Datetime|null
     */
    private function getDeliveryDateTime($trackInfo): \Datetime|null
    {
        $timestamp = null;
        if (!empty($trackInfo['dateAndTimes']) && is_array($trackInfo['dateAndTimes'])) {
            foreach ($trackInfo['dateAndTimes'] as $dateAndTimeInfo) {
                if (!empty($dateAndTimeInfo['type']) &&
                    ($dateAndTimeInfo['type'] == 'ESTIMATED_DELIVERY' || $dateAndTimeInfo['type'] == 'ACTUAL_DELIVERY')
                    && !empty($dateAndTimeInfo['dateTime'])
                ) {
                    $timestamp = $this->parseDate($dateAndTimeInfo['dateTime']);
                    break;
                }
            }
        }

        return $timestamp ?: null;
    }

    /**
     * Get delivery address details in string representation Return City, State, Country Code
     *
     * @param array $address
     * @return \Magento\Framework\Phrase|string
     */
    private function getDeliveryAddress($address): \Magento\Framework\Phrase|string
    {
        $details = [];

        if (!empty($address['city'])) {
            $details[] = (string) $address['city'];
        }

        if (!empty($address['stateOrProvinceCode'])) {
            $details[] = (string) $address['stateOrProvinceCode'];
        }

        if (!empty($address['countryCode'])) {
            $details[] = (string) $address['countryCode'];
        }

        return implode(', ', $details);
    }

    /**
     * Parse tracking details events from response
     * Return list of items in such format:
     * ['activity', 'deliverydate', 'deliverytime', 'deliverylocation']
     *
     * @param array $events
     * @return array
     */
    private function processTrackDetailsEvents(array $events): array
    {
        $result = [];
        foreach ($events as $event) {
            $item = [
                'activity' => (string) $event['eventDescription'],
                'deliverydate' => null,
                'deliverytime' => null,
                'deliverylocation' => null
            ];

            $datetime = $this->parseDate(!empty($event['date']) ? $event['date'] : null);
            if ($datetime) {
                $item['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
                $item['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
            }

            if (!empty($event['scanLocation'])) {
                $item['deliverylocation'] = $this->getDeliveryAddress($event['scanLocation']);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Append error message to rate result instance
     *
     * @param string $trackingValue
     * @param string $errorMessage
     */
    private function appendTrackingError($trackingValue, $errorMessage)
    {
        $error = $this->_trackErrorFactory->create();
        $error->setCarrier('fedex');
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setTracking($trackingValue);
        $error->setErrorMessage($errorMessage);
        $result = $this->getResult();
        $result->append($error);
    }

    /**
     * Parses datetime string from FedEx response.
     * According to FedEx API, datetime string should be in \DateTime::ATOM format, but
     * sometimes FedEx returns datetime without timezone and in that case timezone will be set as UTC.
     *
     * @param string $timestamp
     * @return bool|\DateTime
     */
    private function parseDate($timestamp)
    {
        if ($timestamp === null) {
            return false;
        }
        $formats = [\DateTime::ATOM, 'Y-m-d\TH:i:s'];
        foreach ($formats as $format) {
            // set UTC timezone for a case if timestamp does not contain any timezone
            $utcTimezone = new \DateTimeZone('UTC');
            $dateTime = \DateTime::createFromFormat($format, $timestamp, $utcTimezone);
            if ($dateTime !== false) {
                return $dateTime;
            }
        }

        return false;
    }

    /**
     * Defines payment type by request. Two values are available: RECIPIENT or SENDER.
     *
     * @param DataObject $request
     * @return string
     */
    private function getPaymentType(DataObject $request): string
    {
        return $request->getIsReturn() && $request->getShippingMethod() !== self::RATE_REQUEST_SMARTPOST
            ? 'RECIPIENT'
            : 'SENDER';
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
                $packages[$i]['weight'] = $dividedWeight;
            }
        }
        $this->_numBoxes = count($packages);

        return $packages;
    }
}
