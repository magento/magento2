<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Fedex\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Dir;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;

/**
 * Fedex shipping implementation
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'fedex';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    const RATE_REQUEST_GENERAL = 'general';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    const RATE_REQUEST_SMARTPOST = 'SMART_POST';

    /**
     * Code of the carrier
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = self::CODE;

    /**
     * Types of rates, order is important
     *
     * @var array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $_request = null;

    /**
     * Rate result data
     *
     * @var Result|null
     * @since 2.0.0
     */
    protected $_result = null;

    /**
     * Path to wsdl file of rate service
     *
     * @var string
     * @since 2.0.0
     */
    protected $_rateServiceWsdl;

    /**
     * Path to wsdl file of ship service
     *
     * @var string
     * @since 2.0.0
     */
    protected $_shipServiceWsdl = null;

    /**
     * Path to wsdl file of track service
     *
     * @var string
     * @since 2.0.0
     */
    protected $_trackServiceWsdl = null;

    /**
     * Container types that could be customized for FedEx carrier
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_customizableContainerTypes = ['YOUR_PACKAGING'];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * @since 2.0.0
     */
    protected $_productCollectionFactory;

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    protected $_debugReplacePrivateDataKeys = [
        'Key', 'Password', 'MeterNumber',
    ];

    /**
     * Version of tracking service
     * @var int
     * @since 2.2.0
     */
    private static $trackServiceVersion = 10;

    /**
     * List of TrackReply errors
     * @var array
     * @since 2.2.0
     */
    private static $trackingErrors = ['FAILURE', 'ERROR'];

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

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
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     * @param Json|null $serializer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
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
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = [],
        Json $serializer = null
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
        $wsdlBasePath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Fedex') . '/wsdl/';
        $this->_shipServiceWsdl = $wsdlBasePath . 'ShipService_v10.wsdl';
        $this->_rateServiceWsdl = $wsdlBasePath . 'RateService_v10.wsdl';
        $this->_trackServiceWsdl = $wsdlBasePath . 'TrackService_v' . self::$trackServiceVersion . '.wsdl';
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @return \SoapClient
     * @since 2.0.0
     */
    protected function _createSoapClient($wsdl, $trace = false)
    {
        $client = new \SoapClient($wsdl, ['trace' => $trace]);
        $client->__setLocation(
            $this->getConfigFlag(
                'sandbox_mode'
            ) ? $this->getConfigData('sandbox_webservices_url') : $this->getConfigData('production_webservices_url')
        );

        return $client;
    }

    /**
     * Create rate soap client
     *
     * @return \SoapClient
     * @since 2.0.0
     */
    protected function _createRateSoapClient()
    {
        return $this->_createSoapClient($this->_rateServiceWsdl);
    }

    /**
     * Create ship soap client
     *
     * @return \SoapClient
     * @since 2.0.0
     */
    protected function _createShipSoapClient()
    {
        return $this->_createSoapClient($this->_shipServiceWsdl, 1);
    }

    /**
     * Create track soap client
     *
     * @return \SoapClient
     * @since 2.0.0
     */
    protected function _createTrackSoapClient()
    {
        return $this->_createSoapClient($this->_trackServiceWsdl, 1);
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool|null
     * @since 2.0.0
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
     * @since 2.0.0
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

        if ($request->getFedexDropoff()) {
            $dropoff = $request->getFedexDropoff();
        } else {
            $dropoff = $this->getConfigData('dropoff');
        }
        $r->setDropoffType($dropoff);

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
        } else {
        }

        if ($request->getDestCity()) {
            $r->setDestCity($request->getDestCity());
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeight($weight);
        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackagePhysicalValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

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
     * @since 2.0.0
     */
    public function getResult()
    {
        if (!$this->_result) {
            $this->_result = $this->_trackFactory->create();
        }
        return $this->_result;
    }

    /**
     * Get version of rates request
     *
     * @return array
     * @since 2.0.0
     */
    public function getVersionInfo()
    {
        return ['ServiceId' => 'crs', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0'];
    }

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     * @return array
     * @since 2.0.0
     */
    protected function _formRateRequest($purpose)
    {
        $r = $this->_rawRequest;
        $ratesRequest = [
            'WebAuthenticationDetail' => [
                'UserCredential' => ['Key' => $r->getKey(), 'Password' => $r->getPassword()],
            ],
            'ClientDetail' => ['AccountNumber' => $r->getAccount(), 'MeterNumber' => $r->getMeterNumber()],
            'Version' => $this->getVersionInfo(),
            'RequestedShipment' => [
                'DropoffType' => $r->getDropoffType(),
                'ShipTimestamp' => date('c'),
                'PackagingType' => $r->getPackaging(),
                'TotalInsuredValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                'Shipper' => [
                    'Address' => ['PostalCode' => $r->getOrigPostal(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'Recipient' => [
                    'Address' => [
                        'PostalCode' => $r->getDestPostal(),
                        'CountryCode' => $r->getDestCountry(),
                        'Residential' => (bool)$this->getConfigData('residence_delivery'),
                    ],
                ],
                'ShippingChargesPayment' => [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['AccountNumber' => $r->getAccount(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'CustomsClearanceDetail' => [
                    'CustomsValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                ],
                'RateRequestTypes' => 'LIST',
                'PackageCount' => '1',
                'PackageDetail' => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' => [
                    '0' => [
                        'Weight' => [
                            'Value' => (double)$r->getWeight(),
                            'Units' => $this->getConfigData('unit_of_measure'),
                        ],
                        'GroupPackageCount' => 1,
                    ],
                ],
            ],
        ];

        if ($r->getDestCity()) {
            $ratesRequest['RequestedShipment']['Recipient']['Address']['City'] = $r->getDestCity();
        }

        if ($purpose == self::RATE_REQUEST_GENERAL) {
            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][0]['InsuredValue'] = [
                'Amount' => $r->getValue(),
                'Currency' => $this->getCurrencyCode(),
            ];
        } else {
            if ($purpose == self::RATE_REQUEST_SMARTPOST) {
                $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
                $ratesRequest['RequestedShipment']['SmartPostDetail'] = [
                    'Indicia' => (double)$r->getWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                    'HubId' => $this->getConfigData('smartpost_hubid'),
                ];
            }
        }

        return $ratesRequest;
    }

    /**
     * Makes remote request to the carrier and returns a response
     *
     * @param string $purpose
     * @return mixed
     * @since 2.0.0
     */
    protected function _doRatesRequest($purpose)
    {
        $ratesRequest = $this->_formRateRequest($purpose);
        $ratesRequestNoShipTimestamp = $ratesRequest;
        unset($ratesRequestNoShipTimestamp['RequestedShipment']['ShipTimestamp']);
        $requestString = $this->serializer->serialize($ratesRequestNoShipTimestamp);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = ['request' => $this->filterDebugData($ratesRequest)];
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();
                $response = $client->getRates($ratesRequest);
                $this->_setCachedQuotes($requestString, $response);
                $debugData['result'] = $response;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $this->_logger->critical($e);
            }
        } else {
            $debugData['result'] = $response;
        }
        $this->_debug($debugData);

        return $response;
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Result
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _prepareRateResponse($response)
    {
        $costArr = [];
        $priceArr = [];
        $errorTitle = 'For some reason we can\'t retrieve tracking info right now.';

        if (is_object($response)) {
            if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
                if (is_array($response->Notifications)) {
                    $notification = array_pop($response->Notifications);
                    $errorTitle = (string)$notification->Message;
                } else {
                    $errorTitle = (string)$response->Notifications->Message;
                }
            } elseif (isset($response->RateReplyDetails)) {
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                if (is_array($response->RateReplyDetails)) {
                    foreach ($response->RateReplyDetails as $rate) {
                        $serviceName = (string)$rate->ServiceType;
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = $this->_getRateAmountOriginBased($rate);
                            $costArr[$serviceName] = $amount;
                            $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                        }
                    }
                    asort($priceArr);
                } else {
                    $rate = $response->RateReplyDetails;
                    $serviceName = (string)$rate->ServiceType;
                    if (in_array($serviceName, $allowedMethods)) {
                        $amount = $this->_getRateAmountOriginBased($rate);
                        $costArr[$serviceName] = $amount;
                        $priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
                    }
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
     * Get origin based amount form response of rate estimation
     *
     * @param \stdClass $rate
     * @return null|float
     * @since 2.0.0
     */
    protected function _getRateAmountOriginBased($rate)
    {
        $amount = null;
        $rateTypeAmounts = [];

        if (is_object($rate)) {
            // The "RATED..." rates are expressed in the currency of the origin country
            foreach ($rate->RatedShipmentDetails as $ratedShipmentDetail) {
                $netAmount = (string)$ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount;
                $rateType = (string)$ratedShipmentDetail->ShipmentRateDetail->RateType;
                $rateTypeAmounts[$rateType] = $netAmount;
            }

            foreach ($this->_ratesOrder as $rateType) {
                if (!empty($rateTypeAmounts[$rateType])) {
                    $amount = $rateTypeAmounts[$rateType];
                    break;
                }
            }

            if (is_null($amount)) {
                $amount = (string)$rate->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
            }
        }

        return $amount;
    }

    /**
     * Set free method request
     *
     * @param string $freeMethod
     * @return void
     * @since 2.0.0
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        $r = $this->_rawRequest;
        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $r->setWeight($weight);
        $r->setService($freeMethod);
    }

    /**
     * Get xml quotes
     *
     * @return Result
     * @since 2.0.0
     */
    protected function _getXmlQuotes()
    {
        $r = $this->_rawRequest;
        $xml = $this->_xmlElFactory->create(
            ['data' => '<?xml version = "1.0" encoding = "UTF-8"?><FDXRateAvailableServicesRequest/>']
        );

        $xml->addAttribute('xmlns:api', 'http://www.fedex.com/fsmapi');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'FDXRateAvailableServicesRequest.xsd');

        $requestHeader = $xml->addChild('RequestHeader');
        $requestHeader->addChild('AccountNumber', $r->getAccount());
        $requestHeader->addChild('MeterNumber', '0');

        $xml->addChild('ShipDate', date('Y-m-d'));
        $xml->addChild('DropoffType', $r->getDropoffType());
        if ($r->hasService()) {
            $xml->addChild('Service', $r->getService());
        }
        $xml->addChild('Packaging', $r->getPackaging());
        $xml->addChild('WeightUnits', 'LBS');
        $xml->addChild('Weight', $r->getWeight());

        $originAddress = $xml->addChild('OriginAddress');
        $originAddress->addChild('PostalCode', $r->getOrigPostal());
        $originAddress->addChild('CountryCode', $r->getOrigCountry());

        $destinationAddress = $xml->addChild('DestinationAddress');
        $destinationAddress->addChild('PostalCode', $r->getDestPostal());
        $destinationAddress->addChild('CountryCode', $r->getDestCountry());

        $payment = $xml->addChild('Payment');
        $payment->addChild('PayorType', 'SENDER');

        $declaredValue = $xml->addChild('DeclaredValue');
        $declaredValue->addChild('Value', $r->getValue());
        $declaredValue->addChild('CurrencyCode', $this->getCurrencyCode());

        if ($this->getConfigData('residence_delivery')) {
            $specialServices = $xml->addChild('SpecialServices');
            $specialServices->addChild('ResidentialDelivery', 'true');
        }

        $xml->addChild('PackageCount', '1');

        $request = $xml->asXML();

        $responseBody = $this->_getCachedQuotes($request);
        if ($responseBody === null) {
            $debugData = ['request' => $request];
            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultGatewayUrl;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                $responseBody = curl_exec($ch);
                curl_close($ch);

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
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _parseXmlResponse($response)
    {
        $costArr = [];
        $priceArr = [];

        if (strlen(trim($response)) > 0) {
            $xml = $this->parseXml($response, \Magento\Shipping\Model\Simplexml\Element::class);
            if (is_object($xml)) {
                if (is_object($xml->Error) && is_object($xml->Error->Message)) {
                    $errorTitle = (string)$xml->Error->Message;
                } elseif (is_object($xml->SoftError) && is_object($xml->SoftError->Message)) {
                    $errorTitle = (string)$xml->SoftError->Message;
                } else {
                    $errorTitle = 'Sorry, something went wrong. Please try again or contact us and we\'ll try to help.';
                }

                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                foreach ($xml->Entry as $entry) {
                    if (in_array((string)$entry->Service, $allowedMethods)) {
                        $costArr[(string)$entry->Service] = (string)$entry
                            ->EstimatedCharges
                            ->DiscountedCharges
                            ->NetCharge;
                        $priceArr[(string)$entry->Service] = $this->getMethodPrice(
                            (string)$entry->EstimatedCharges->DiscountedCharges->NetCharge,
                            (string)$entry->Service
                        );
                    }
                }

                asort($priceArr);
            } else {
                $errorTitle = 'Response is in the wrong format.';
            }
        } else {
            $errorTitle = 'For some reason we can\'t retrieve tracking info right now.';
        }

        $result = $this->_rateFactory->create();
        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('fedex');
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
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function getCode($type, $code = '')
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
     * @since 2.0.0
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

        return isset($codes[$currencyCode]) ? $codes[$currencyCode] : $currencyCode;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     * @since 2.0.0
     */
    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        foreach ($trackings as $tracking) {
            $this->_getXMLTracking($tracking);
        }

        return $this->_result;
    }

    /**
     * Set tracking request
     *
     * @return void
     * @since 2.0.0
     */
    protected function setTrackingReqeust()
    {
        $r = new \Magento\Framework\DataObject();

        $account = $this->getConfigData('account');
        $r->setAccount($account);

        $this->_rawTrackingRequest = $r;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $tracking
     * @return void
     * @since 2.0.0
     */
    protected function _getXMLTracking($tracking)
    {
        $trackRequest = [
            'WebAuthenticationDetail' => [
                'UserCredential' => [
                    'Key' => $this->getConfigData('key'),
                    'Password' => $this->getConfigData('password'),
                ],
            ],
            'ClientDetail' => [
                'AccountNumber' => $this->getConfigData('account'),
                'MeterNumber' => $this->getConfigData('meter_number'),
            ],
            'Version' => [
                'ServiceId' => 'trck',
                'Major' => self::$trackServiceVersion,
                'Intermediate' => '0',
                'Minor' => '0',
            ],
            'SelectionDetails' => [
                'PackageIdentifier' => ['Type' => 'TRACKING_NUMBER_OR_DOORTAG', 'Value' => $tracking],
            ],
            'ProcessingOptions' => 'INCLUDE_DETAILED_SCANS'
        ];
        $requestString = $this->serializer->serialize($trackRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = ['request' => $trackRequest];
        if ($response === null) {
            try {
                $client = $this->_createTrackSoapClient();
                $response = $client->track($trackRequest);
                $this->_setCachedQuotes($requestString, $response);
                $debugData['result'] = $response;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $this->_logger->critical($e);
            }
        } else {
            $debugData['result'] = $response;
        }
        $this->_debug($debugData);

        $this->_parseTrackingResponse($tracking, $response);
    }

    /**
     * Parse tracking response
     *
     * @param string $trackingValue
     * @param \stdClass $response
     * @return void
     * @since 2.0.0
     */
    protected function _parseTrackingResponse($trackingValue, $response)
    {
        if (!is_object($response) || empty($response->HighestSeverity)) {
            $this->appendTrackingError($trackingValue, __('Invalid response from carrier'));
            return;
        } elseif (in_array($response->HighestSeverity, self::$trackingErrors)) {
            $this->appendTrackingError($trackingValue, (string) $response->Notifications->Message);
            return;
        } elseif (empty($response->CompletedTrackDetails) || empty($response->CompletedTrackDetails->TrackDetails)) {
            $this->appendTrackingError($trackingValue, __('No available tracking items'));
            return;
        }

        $trackInfo = $response->CompletedTrackDetails->TrackDetails;

        // Fedex can return tracking details as single object instead array
        if (is_object($trackInfo)) {
            $trackInfo = [$trackInfo];
        }

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
                $trackingValue, __('For some reason we can\'t retrieve tracking info right now.')
            );
        }
    }

    /**
     * Get tracking response
     *
     * @return string
     * @since 2.0.0
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
        if (empty($statuses)) {
            $statuses = __('Empty response');
        }

        return $statuses;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     * @since 2.0.0
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
     * Return array of authenticated information
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getAuthDetails()
    {
        return [
            'WebAuthenticationDetail' => [
                'UserCredential' => [
                    'Key' => $this->getConfigData('key'),
                    'Password' => $this->getConfigData('password'),
                ],
            ],
            'ClientDetail' => [
                'AccountNumber' => $this->getConfigData('account'),
                'MeterNumber' => $this->getConfigData('meter_number'),
            ],
            'TransactionDetail' => [
                'CustomerTransactionId' => '*** Express Domestic Shipping Request v9 using PHP ***',
            ],
            'Version' => ['ServiceId' => 'ship', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0'],
        ];
    }

    /**
     * Form array with appropriate structure for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
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
        $weightUnits = $packageParams->getWeightUnits() == \Zend_Measure_Weight::POUND ? 'LB' : 'KG';
        $unitPrice = 0;
        $itemsQty = 0;
        $itemsDesc = [];
        $countriesOfManufacture = [];
        $productIds = [];
        $packageItems = $request->getPackageItems();
        foreach ($packageItems as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);

            $unitPrice += $item->getPrice();
            $itemsQty += $item->getQty();

            $itemsDesc[] = $item->getName();
            $productIds[] = $item->getProductId();
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

        $paymentType = $request->getIsReturn() ? 'RECIPIENT' : 'SENDER';
        $optionType = $request->getShippingMethod() == self::RATE_REQUEST_SMARTPOST
            ? 'SERVICE_DEFAULT' : $packageParams->getDeliveryConfirmation();
        $requestClient = [
            'RequestedShipment' => [
                'ShipTimestamp' => time(),
                'DropoffType' => $this->getConfigData('dropoff'),
                'PackagingType' => $request->getPackagingType(),
                'ServiceType' => $request->getShippingMethod(),
                'Shipper' => [
                    'Contact' => [
                        'PersonName' => $request->getShipperContactPersonName(),
                        'CompanyName' => $request->getShipperContactCompanyName(),
                        'PhoneNumber' => $request->getShipperContactPhoneNumber(),
                    ],
                    'Address' => [
                        'StreetLines' => [$request->getShipperAddressStreet()],
                        'City' => $request->getShipperAddressCity(),
                        'StateOrProvinceCode' => $request->getShipperAddressStateOrProvinceCode(),
                        'PostalCode' => $request->getShipperAddressPostalCode(),
                        'CountryCode' => $request->getShipperAddressCountryCode(),
                    ],
                ],
                'Recipient' => [
                    'Contact' => [
                        'PersonName' => $request->getRecipientContactPersonName(),
                        'CompanyName' => $request->getRecipientContactCompanyName(),
                        'PhoneNumber' => $request->getRecipientContactPhoneNumber(),
                    ],
                    'Address' => [
                        'StreetLines' => [$request->getRecipientAddressStreet()],
                        'City' => $request->getRecipientAddressCity(),
                        'StateOrProvinceCode' => $request->getRecipientAddressStateOrProvinceCode(),
                        'PostalCode' => $request->getRecipientAddressPostalCode(),
                        'CountryCode' => $request->getRecipientAddressCountryCode(),
                        'Residential' => (bool)$this->getConfigData('residence_delivery'),
                    ],
                ],
                'ShippingChargesPayment' => [
                    'PaymentType' => $paymentType,
                    'Payor' => [
                        'AccountNumber' => $this->getConfigData('account'),
                        'CountryCode' => $this->_scopeConfig->getValue(
                            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $request->getStoreId()
                        ),
                    ],
                ],
                'LabelSpecification' => [
                    'LabelFormatType' => 'COMMON2D',
                    'ImageType' => 'PNG',
                    'LabelStockType' => 'PAPER_8.5X11_TOP_HALF_LABEL',
                ],
                'RateRequestTypes' => ['ACCOUNT'],
                'PackageCount' => 1,
                'RequestedPackageLineItems' => [
                    'SequenceNumber' => '1',
                    'Weight' => ['Units' => $weightUnits, 'Value' => $request->getPackageWeight()],
                    'CustomerReferences' => [
                        'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                        'Value' => $referenceData,
                    ],
                    'SpecialServicesRequested' => [
                        'SpecialServiceTypes' => 'SIGNATURE_OPTION',
                        'SignatureOptionDetail' => ['OptionType' => $optionType],
                    ],
                ],
            ],
        ];

        // for international shipping
        if ($request->getShipperAddressCountryCode() != $request->getRecipientAddressCountryCode()) {
            $requestClient['RequestedShipment']['CustomsClearanceDetail'] = [
                'CustomsValue' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $customsValue],
                'DutiesPayment' => [
                    'PaymentType' => $paymentType,
                    'Payor' => [
                        'AccountNumber' => $this->getConfigData('account'),
                        'CountryCode' => $this->_scopeConfig->getValue(
                            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $request->getStoreId()
                        ),
                    ],
                ],
                'Commodities' => [
                    'Weight' => ['Units' => $weightUnits, 'Value' => $request->getPackageWeight()],
                    'NumberOfPieces' => 1,
                    'CountryOfManufacture' => implode(',', array_unique($countriesOfManufacture)),
                    'Description' => implode(', ', $itemsDesc),
                    'Quantity' => ceil($itemsQty),
                    'QuantityUnits' => 'pcs',
                    'UnitPrice' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $unitPrice],
                    'CustomsValue' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $customsValue],
                ],
            ];
        }

        if ($request->getMasterTrackingId()) {
            $requestClient['RequestedShipment']['MasterTrackingId'] = $request->getMasterTrackingId();
        }

        if ($request->getShippingMethod() == self::RATE_REQUEST_SMARTPOST) {
            $requestClient['RequestedShipment']['SmartPostDetail'] = [
                'Indicia' => (double)$request->getPackageWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                'HubId' => $this->getConfigData('smartpost_hubid'),
            ];
        }

        // set dimensions
        if ($length || $width || $height) {
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['Dimensions'] = [
                'Length' => $length,
                'Width' => $width,
                'Height' => $height,
                'Units' => $packageParams->getDimensionUnits() == \Zend_Measure_Length::INCH ? 'IN' : 'CM',
            ];
        }

        return $this->_getAuthDetails() + $requestClient;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();
        $client = $this->_createShipSoapClient();
        $requestClient = $this->_formShipmentRequest($request);
        $response = $client->processShipment($requestClient);

        if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
            $shippingLabelContent = $response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
            $trackingNumber = $this->getTrackingNumber(
                $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds
            );
            $result->setShippingLabelContent($shippingLabelContent);
            $result->setTrackingNumber($trackingNumber);
            $debugData = ['request' => $client->__getLastRequest(), 'result' => $client->__getLastResponse()];
            $this->_debug($debugData);
        } else {
            $debugData = [
                'request' => $client->__getLastRequest(),
                'result' => ['error' => '', 'code' => '', 'xml' => $client->__getLastResponse()],
            ];
            if (is_array($response->Notifications)) {
                foreach ($response->Notifications as $notification) {
                    $debugData['result']['code'] .= $notification->Code . '; ';
                    $debugData['result']['error'] .= $notification->Message . '; ';
                }
            } else {
                $debugData['result']['code'] = $response->Notifications->Code . ' ';
                $debugData['result']['error'] = $response->Notifications->Message . ' ';
            }
            $this->_debug($debugData);
            $result->setErrors($debugData['result']['error']);
        }
        $result->setGatewayResponse($client->__getLastResponse());

        return $result;
    }

    /**
     * @param array|object $trackingIds
     * @return string
     * @since 2.0.0
     */
    private function getTrackingNumber($trackingIds)
    {
        return is_array($trackingIds) ? array_map(
            function ($val) {
                return $val->TrackingNumber;
            },
            $trackingIds
        ) : $trackingIds->TrackingNumber;
    }

    /**
     * For multi package shipments. Delete requested shipments if the current shipment
     * request is failed
     *
     * @param array $data
     * @return bool
     * @since 2.0.0
     */
    public function rollBack($data)
    {
        $requestData = $this->_getAuthDetails();
        $requestData['DeletionControl'] = 'DELETE_ONE_PACKAGE';
        foreach ($data as &$item) {
            $requestData['TrackingId'] = $item['tracking_number'];
            $client = $this->_createShipSoapClient();
            $client->deleteShipment($requestData);
        }

        return true;
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
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
     * @since 2.0.0
     */
    public function getContainerTypesAll()
    {
        return $this->getCode('packaging');
    }

    /**
     * Return structured data of containers witch related with shipping methods
     *
     * @return array|bool
     * @since 2.0.0
     */
    public function getContainerTypesFilter()
    {
        return $this->getCode('containers_filter');
    }

    /**
     * Return delivery confirmation types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getDeliveryConfirmationTypes(\Magento\Framework\DataObject $params = null)
    {
        return $this->getCode('delivery_confirmation_types');
    }

    /**
     * Recursive replace sensitive fields in debug data by the mask
     * @param string $data
     * @return string
     * @since 2.1.0
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
     * @param \stdClass $trackInfo
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.2.0
     */
    private function processTrackingDetails(\stdClass $trackInfo)
    {
        $result = [
            'shippeddate' => null,
            'deliverydate' => null,
            'deliverytime' => null,
            'deliverylocation' => null,
            'weight' => null,
            'progressdetail' => [],
        ];

        $datetime = $this->parseDate(!empty($trackInfo->ShipTimestamp) ? $trackInfo->ShipTimestamp : null);
        if ($datetime) {
            $result['shippeddate'] = gmdate('Y-m-d', $datetime->getTimestamp());
        }

        $result['signedby'] = !empty($trackInfo->DeliverySignatureName) ?
            (string) $trackInfo->DeliverySignatureName :
            null;

        $result['status'] = (!empty($trackInfo->StatusDetail) && !empty($trackInfo->StatusDetail->Description)) ?
            (string) $trackInfo->StatusDetail->Description :
            null;
        $result['service'] = (!empty($trackInfo->Service) && !empty($trackInfo->Service->Description)) ?
            (string) $trackInfo->Service->Description :
            null;

        $datetime = $this->getDeliveryDateTime($trackInfo);
        if ($datetime) {
            $result['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
            $result['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
        }

        $address = null;
        if (!empty($trackInfo->EstimatedDeliveryAddress)) {
            $address = $trackInfo->EstimatedDeliveryAddress;
        } elseif (!empty($trackInfo->ActualDeliveryAddress)) {
            $address = $trackInfo->ActualDeliveryAddress;
        }

        if (!empty($address)) {
            $result['deliverylocation'] = $this->getDeliveryAddress($address);
        }

        if (!empty($trackInfo->PackageWeight)) {
            $result['weight'] = sprintf(
                '%s %s',
                (string) $trackInfo->PackageWeight->Value,
                (string) $trackInfo->PackageWeight->Units
            );
        }

        if (!empty($trackInfo->Events)) {
            $events = $trackInfo->Events;
            if (is_object($events)) {
                $events = [$trackInfo->Events];
            }
            $result['progressdetail'] = $this->processTrackDetailsEvents($events);
        }

        return $result;
    }

    /**
     * Parse delivery datetime from tracking details
     * @param \stdClass $trackInfo
     * @return \Datetime|null
     * @since 2.2.0
     */
    private function getDeliveryDateTime(\stdClass $trackInfo)
    {
        $timestamp = null;
        if (!empty($trackInfo->EstimatedDeliveryTimestamp)) {
            $timestamp = $trackInfo->EstimatedDeliveryTimestamp;
        } elseif (!empty($trackInfo->ActualDeliveryTimestamp)) {
            $timestamp = $trackInfo->ActualDeliveryTimestamp;
        }

        return $timestamp ? $this->parseDate($timestamp) : null;
    }

    /**
     * Get delivery address details in string representation
     * Return City, State, Country Code
     *
     * @param \stdClass $address
     * @return \Magento\Framework\Phrase|string
     * @since 2.2.0
     */
    private function getDeliveryAddress(\stdClass $address)
    {
        $details = [];

        if (!empty($address->City)) {
            $details[] = (string) $address->City;
        }

        if (!empty($address->StateOrProvinceCode)) {
            $details[] = (string) $address->StateOrProvinceCode;
        }

        if (!empty($address->CountryCode)) {
            $details[] = (string) $address->CountryCode;
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
     * @since 2.2.0
     */
    private function processTrackDetailsEvents(array $events)
    {
        $result = [];
        /** @var \stdClass $event */
        foreach ($events as $event) {
            $item = [
                'activity' => (string) $event->EventDescription,
                'deliverydate' => null,
                'deliverytime' => null,
                'deliverylocation' => null
            ];

            $datetime = $this->parseDate(!empty($event->Timestamp) ? $event->Timestamp : null);
            if ($datetime) {
                $item['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
                $item['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
            }

            if (!empty($event->Address)) {
                $item['deliverylocation'] = $this->getDeliveryAddress($event->Address);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Append error message to rate result instance
     * @param string $trackingValue
     * @param string $errorMessage
     * @since 2.2.0
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
     * @since 2.2.0
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
}
