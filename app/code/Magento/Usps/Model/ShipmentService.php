<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Usps\Model;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Async\CallbackDeferred;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\Measure\Exception\MeasureException;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\Measure\Length;
use Magento\Framework\Measure\Weight;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Shipping\Model\Rate\Result\ProxyDeferredFactory;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 * @SuppressWarnings("PHPMD.NPathComplexity")
 */
class ShipmentService
{
    /**
     * REST end point for Shipment API
     *
     * @var string
     */
    public const SHIPMENT_REQUEST_END_POINT = 'shipments/v3/options/search';

    /**
     * REST end point to Create Domestic Label
     *
     * @var string
     */
    public const DOMESTIC_SHIPMENT_LABEL_REQUEST_END_POINT = 'labels/v3/label';

    /**
     * REST end point to Create International Label
     *
     * @var string
     */
    public const INTERNATIONAL_SHIPMENT_LABEL_REQUEST_END_POINT = 'international-labels/v3/international-label';

    /**
     * REST end point for Shipment API
     *
     * @var string
     */
    public const PAYMENT_AUTH_REQUEST_END_POINT = 'payments/v3/payment-authorization';

    private const CONTENT_TYPE_JSON = 'application/json';
    private const AUTHORIZATION_BEARER = 'Bearer ';
    private const ERROR_LOG_MESSAGE = '---Exception from auth api---';
    private const ACCEPT_HEADER = 'application/vnd.usps.labels+json';

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $_productCollectionFactory;

    /**
     * @var Carrier
     */
    private Carrier $carrierModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * The carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    private $_carrierHelper;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $_rateFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $_rateMethodFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory
     */
    private $_rateErrorFactory;

    /**
     * @var AsyncClientInterface
     */
    private $httpClient;

    /**
     * @var ProxyDeferredFactory
     */
    private $proxyDeferredFactory;

    /**
     * @var UspsPaymentAuthToken
     */
    public UspsPaymentAuthToken $uspsPaymentAuthToken;

    /**
     * @var ShippingMethodManager
     */
    private ShippingMethodManager $shippingMethodManager;

    /**
     * Shipment Service Constructor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param CollectionFactory $productCollection
     * @param CarrierHelper $carrierHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param UspsPaymentAuthToken $uspsPaymentAuthToken
     * @param ShippingMethodManager $shippingMethodManager
     * @param AsyncClientInterface|null $httpClient
     * @param ProxyDeferredFactory|null $proxyDeferredFactory
     */
    public function __construct(
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        CollectionFactory $productCollection,
        CarrierHelper $carrierHelper,
        \Psr\Log\LoggerInterface $logger,
        UspsPaymentAuthToken $uspsPaymentAuthToken,
        ShippingMethodManager $shippingMethodManager,
        ?AsyncClientInterface $httpClient = null,
        ?ProxyDeferredFactory $proxyDeferredFactory = null,
    ) {
        $this->_rateFactory = $rateFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->_productCollectionFactory = $productCollection;
        $this->_carrierHelper = $carrierHelper;
        $this->_logger = $logger;
        $this->uspsPaymentAuthToken = $uspsPaymentAuthToken;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->httpClient = $httpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
        $this->proxyDeferredFactory = $proxyDeferredFactory
            ?? ObjectManager::getInstance()->get(ProxyDeferredFactory::class);
    }

    /**
     * Set carrier model
     *
     * @param Carrier $carrierModel
     * @return void
     */
    public function setCarrierModel(Carrier $carrierModel) : void
    {
        $this->carrierModel = $carrierModel;
    }

    /**
     * Build RateV3 request, send it to USPS gateway and retrieve quotes in JSON format
     *
     * @link https://developer.usps.com/shippingoptionsv3
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws LocalizedException
     */
    public function getJsonQuotes(): Result
    {
        $request = $this->carrierModel->getRawRequest();
        $accessToken = $this->carrierModel->getOauthAccessRequest();
        // The origin address(shipper) must be only in USA
        if (!$this->carrierModel->_isUSCountry($request->getOrigCountryId())) {
            $responseBody = [];
            return $this->_parseJsonResponse($responseBody);
        }
        $priceType = $this->carrierModel->getConfigData('price_type');
        $requestParam = [
            "originZIPCode" => $request->getOrigPostal(),
            'pricingOptions' => [
                [
                    "priceType" => $priceType
                ]
            ],
        ];

        foreach ($request->getPackages() as $packageData) {
            $requestParam['packageDescription'] = [
                "weight" => $packageData['weight_pounds'] ?? 1,
                "mailClass" => 'ALL'
            ];
            $requestParam['packageDescription']['length'] = $request->getLength() ? (int) $request->getLength() : 1;
            $requestParam['packageDescription']['height'] = $request->getHeight() ? (int) $request->getHeight() : 1;
            $requestParam['packageDescription']['width']  = $request->getWidth()  ? (int) $request->getWidth()  : 1;

            if ($request->getContainer() == 'NONRECTANGULAR' || $request->getContainer() == 'VARIABLE') {
                $requestParam['packageDescription']['girth'] = $request->getGirth() ? (int) $request->getGirth() : 1;
            }

            if ($this->carrierModel->_isUSCountry($request->getDestCountryId())) {
                $requestParam['destinationZIPCode'] = is_string($request->getDestPostal()) ?
                    substr($request->getDestPostal(), 0, 5) : '';
            } else {
                $requestParam['destinationCountryCode'] = $request->getDestCountryId();
                $requestParam['foreignPostalCode'] = $request->getDestPostal() ?? '';
            }
        }

        $headers = [
            'Content-Type' => self::CONTENT_TYPE_JSON,
            'Authorization' => self::AUTHORIZATION_BEARER . $accessToken
        ];

        $responseBody = $this->carrierModel->getCachedQuotes(json_encode($requestParam));

        if ($responseBody === null) {
            $debugData = ['request' => $this->carrierModel->filterJsonDebugData($requestParam)];
            $url = $this->carrierModel->getUrl(self::SHIPMENT_REQUEST_END_POINT);

            $deferredResponse = $this->httpClient->request(new Request(
                $url,
                Request::METHOD_POST,
                $headers,
                json_encode($requestParam)
            ));

            return $this->proxyDeferredFactory->create(
                [
                    'deferred' => new CallbackDeferred(
                        function () use ($deferredResponse, $requestParam, $debugData) {
                            $responseResult = null;
                            try {
                                $responseResult = $deferredResponse->get();
                            } catch (HttpException $exception) {
                                $this->_logger->critical(
                                    'Critical error: ' . $exception->getMessage(),
                                    ['exception' => $exception]
                                );
                            }
                            $responseBody = $responseResult ? $responseResult->getBody() : '';
                            $debugData['result'] = $responseBody;
                            $this->carrierModel->setCachedQuotes(json_encode($requestParam), $responseBody);
                            $this->carrierModel->_debug($debugData);

                            return $this->_parseJsonResponse(json_decode($responseBody, true));
                        }
                    )
                ]
            );
        }
        return $this->_parseJsonResponse(json_decode($responseBody, true));
    }

    /**
     * Parse calculated rates for JSON response
     *
     * @param array $response
     * @return Result
     * @link http://www.usps.com/webtools/htm/Rate-Calculators-v2-3.htm
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    public function _parseJsonResponse($response): Result
    {
        $costArr = [];
        if (!empty($response)) {
            $shippingRates = $this->getShippingOptions($response);
            foreach ($shippingRates as $shippingRate) {
                foreach ($shippingRate as $rateElement) {
                    $this->processShippingRateForItem(
                        $rateElement,
                        $costArr
                    );
                }
            }
            uasort($costArr, function ($previous, $next) {
                return ($previous <= $next) ? -1 : 1;
            });
        }

        $result = $this->_rateFactory->create();
        if (empty($costArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('usps');
            $error->setCarrierTitle($this->carrierModel->getConfigData('title'));
            $error->setErrorMessage($this->carrierModel->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($costArr as $method) {
                $rate = $this->_rateMethodFactory->create();

                /** @var Method $method */
                $rate->setCarrier('usps');
                $rate->setCarrierTitle($this->carrierModel->getConfigData('title'));

                $rate->setMethod($method['code']);
                $rate->setMethodTitle($method['productName']);

                $shippingCost = (float)$method['price'];
                $rate->setCost($shippingCost);
                $rate->setPrice($this->carrierModel->getMethodPrice($shippingCost, $method['code']));

                /** @var Result $result */
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Processing rate for ship element
     *
     * @param array $rateElement
     * @param array $costArr
     */
    private function processShippingRateForItem(
        array $rateElement,
        array &$costArr
    ): void {
        if (empty($rateElement['productName']) && empty($rateElement['description'])) {
            return;
        }
        $productName = $rateElement['description'];
        $methodCode = strtoupper(substr($this->replaceSpaceWithUnderscore($productName), 0, 120));
        $methodTitle = $this->shippingMethodManager->getMethodTitle($methodCode);
        $allowedMethods = $this->getRestAllowedMethods();
        if (in_array($methodCode, array_keys($allowedMethods))) {
            // Use totalPrice if available, otherwise use price
            $cost = isset($rateElement['totalPrice']) ?
                (float)$rateElement['totalPrice'] : (float)$rateElement['price'];
            $costArr[$methodCode] = [
                'price' => $cost,
                'code' => $methodCode,
                'productName' => $methodTitle
            ];
        }
    }

    /**
     * Replace space in string with hyphen
     *
     * @param string $string
     * @return string
     */
    public function replaceSpaceWithUnderscore(string $string): string
    {
        return str_replace(' ', '_', $string);
    }

    /**
     * Get shipping options from response
     *
     * @param array $response
     * @return array
     */
    private function getShippingOptions(array $response): array
    {
        $searchKey = 'rates';
        $foundValues = [];
        $shippingOptions = function (array $response) use (&$foundValues, $searchKey, &$shippingOptions) {
            foreach ($response as $key => $value) {
                if ($key === $searchKey) {
                    // Include totalPrice in the rate data
                    foreach ($value as &$rate) {
                        if (isset($response['totalPrice'])) {
                            $rate['totalPrice'] = $response['totalPrice'];
                        }
                    }
                    $foundValues[] = $value;
                }
                if (is_array($value)) {
                    $shippingOptions($value);
                }
            }
        };

        $shippingOptions($response);

        return $foundValues;
    }

    /**
     * Do shipment request for domestic to carrier web service, obtain Print Shipping Labels
     *
     * @param DataObject $request
     * @return array|DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @see no alternatives
     */
    public function _prepareDomesticShipmentLabelRestRequest(DataObject $request): DataObject|array
    {
        $result = new DataObject();
        $shippingMethod = $request->getShippingMethod();
        try {
            $packageParams = $request->getPackageParams();
            $dimensions = $this->preparePackageDimensions($request, $packageParams);
            $height = $dimensions['height'] ?: $request->getPackageHeight();
            $width = $dimensions['width'] ?: $request->getPackageWidth();
            $length = $dimensions['length'] ?: $request->getPackageLength();
            $girth = $dimensions['girth'] ?: $request->getPackageGirth();
            $packagePoundsWeight = (float) $dimensions['weight'] ?: 1;
            list($fromZip5) = $this->_parseZip($request->getShipperAddressPostalCode());
            list($toZip5) = $this->_parseZip($request->getRecipientAddressPostalCode(), true);

            $requestParam = [
                'fromAddress' => [
                    'streetAddress' => $request->getShipperAddressStreet1(),
                    'secondaryAddress' => $request->getShipperAddressStreet2() ?? '',
                    'city' => $request->getShipperAddressCity(),
                    'state' => $request->getShipperAddressStateOrProvinceCode(),
                    'ZIPCode' => $fromZip5,
                    'firstName' => $request->getShipperContactPersonFirstName(),
                    'lastName' => $request->getShipperContactPersonLastName(),
                    'firm' => $request->getShipperContactCompanyName(),
                ],
                'toAddress' => [
                    'streetAddress' => $request->getRecipientAddressStreet1(),
                    'secondaryAddress' => $request->getRecipientAddressStreet2() ?? '',
                    'city' => $request->getRecipientAddressCity(),
                    'state' => $request->getRecipientAddressStateOrProvinceCode(),
                    'ZIPCode' => $toZip5,
                    'firstName' => $request->getRecipientContactPersonFirstName(),
                    'lastName' => $request->getRecipientContactPersonLastName(),
                    'phone' => $request->getRecipientContactPhoneNumber(),
                ],
                'packageDescription' => [
                    'length' => (float) $length,
                    'height' => (float) $height,
                    'width' => (float) $width,
                    'weight' => (float) $packagePoundsWeight,
                    'mailClass' => $this->shippingMethodManager->getMethodMailClass($shippingMethod),
                    'mailingDate' => date('Y-m-d'),
                    'processingCategory' => $this->shippingMethodManager->getMethodProcessingCategory($shippingMethod),
                    "destinationEntryFacilityType" => $this->shippingMethodManager
                        ->getMethodDestinationEntryFacilityType($shippingMethod),
                    'rateIndicator' => $this->shippingMethodManager->getRateIndicator($shippingMethod),
                ]
            ];

            if ($girth > 0) {
                $requestParam['packageDescription']['girth'] = $girth;
            }

            return $requestParam;
        } catch (LocalizedException|Exception $e) {
            $this->_logger->critical($e->getMessage());
            $result->setErrors([$e->getMessage()]);
        }
        return $result;
    }

    /**
     * Do shipment request for international to carrier web service, obtain Print Shipping Labels
     *
     * @param DataObject $request
     * @return array|DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @see no alternatives
     */
    public function _prepareIntlShipmentLabelRestRequest(DataObject $request): DataObject|array
    {
        $result = new DataObject();
        try {
            $recipientCountryCode = $request->getRecipientAddressCountryCode();
            $requestParam = [];
            $packageParams = $request->getPackageParams();
            $this->preparePackageDescForIntl($request, $packageParams, $requestParam);
            $this->prepareCustomFormForIntl($request, $packageParams, $requestParam);

            $requestParam['fromAddress'] = [
                'streetAddress' => $request->getShipperAddressStreet1(),
                'secondaryAddress' => $request->getShipperAddressStreet2() ?? '',
                'city' => $request->getShipperAddressCity(),
                'state' => $request->getShipperAddressStateOrProvinceCode(),
                'ZIPCode' => $request->getShipperAddressPostalCode(),
                'firstName' => $request->getShipperContactPersonFirstName(),
                'lastName' => $request->getShipperContactPersonLastName(),
                'firm' => $request->getShipperContactCompanyName(),
            ];

            $requestParam['toAddress'] = [
                'streetAddress' => $request->getRecipientAddressStreet1(),
                'secondaryAddress' => $request->getRecipientAddressStreet2() ?? '',
                'city' => $request->getRecipientAddressCity(),
                'province' => $request->getRecipientAddressStateOrProvinceCode(),
                'postalCode' => $request->getRecipientAddressPostalCode(),
                'country' => $this->carrierModel->_getCountryName($recipientCountryCode),
                'countryISOAlpha2Code' => $recipientCountryCode,
                'firstName' => $request->getRecipientContactPersonFirstName(),
                'lastName' => $request->getRecipientContactPersonLastName(),
                'phone' => $request->getRecipientContactPhoneNumber()
            ];

            return $requestParam;
        } catch (LocalizedException|Exception $e) {
            $this->_logger->critical($e->getMessage());
            $result->setErrors([$e->getMessage()]);
        }

        return $result;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param DataObject $request
     * @return DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws LocalizedException
     * @throws \Throwable
     */
    public function _doShipmentRequestRest(DataObject $request): DataObject
    {
        try {
            $result = new DataObject();
            $recipientUSCountry = $this->carrierModel->_isUSCountry($request->getRecipientAddressCountryCode());
            $this->setPackageRequest($request);
            $accessToken = $this->carrierModel->getOauthAccessRequest();

            if (!$accessToken) {
                throw new LocalizedException(__('We couldn\'t connect to USPS at the moment.
                 Please try again shortly.'));
            }

            $paymentToken = $this->getLabelPaymentTokenRequest($accessToken);
        } catch (LocalizedException|Exception $e) {
            $this->_logger->critical($e->getMessage());
            $result = new DataObject();
            $result->setErrors([$e->getMessage()]);
            return $result;
        }

        $headers = [
            'Content-Type' => self::CONTENT_TYPE_JSON,
            'Accept' => self::ACCEPT_HEADER,
            'Authorization' => self::AUTHORIZATION_BEARER . $accessToken,
            'X-Payment-Authorization-Token' => $paymentToken
        ];

        $url = $this->carrierModel->getUrl();
        if ($recipientUSCountry) {
            $requestRest = $this->_prepareDomesticShipmentLabelRestRequest($request);
            $url .= self::DOMESTIC_SHIPMENT_LABEL_REQUEST_END_POINT;
        } else {
            $requestRest = $this->_prepareIntlShipmentLabelRestRequest($request);
            $url .= self::INTERNATIONAL_SHIPMENT_LABEL_REQUEST_END_POINT;
        }

        if ($requestRest instanceof DataObject && $requestRest->getErrors()) {
            $result->setErrors($requestRest->getErrors());
            return $result;
        }

        $asyncResponse = $this->httpClient->request(new Request(
            $url,
            Request::METHOD_POST,
            $headers,
            json_encode($requestRest)
        ));

        $responseResult = $asyncResponse->get();
        $response = json_decode($responseResult->getBody(), true);

        if ($responseResult->getStatusCode() === 200 || $responseResult->getStatusCode() === 201) {
            // phpcs:disable Magento2.Functions.DiscouragedFunction
            $labelContent = base64_decode((string)$response['labelImage']);
            $result->setShippingLabelContent($labelContent);
            if ($recipientUSCountry) {
                $trackingNumber = (string)$response['trackingNumber'];
            } else {
                $trackingNumber = (string)$response['internationalTrackingNumber'];
            }
            $result->setTrackingNumber($trackingNumber);
            $result->setGatewayResponse($response);
            $debugData['result'] = $response;
            $this->carrierModel->_debug($debugData);
        } else {
            $errorMsg = $this->handleErrorResponse($response);
            if (empty($errorMsg)) {
                $errorMsg[] = $response['error']['message']
                    ?? __('An error occurred while processing your request.');
            }
            $debugData['result'] = [
                'error' => $errorMsg,
                'code' => $response['error']['code'],
                'request' => $response,
            ];
            $this->carrierModel->_debug($debugData);
            $result->setErrors($debugData['result']['error']);
        }
        return $result;
    }

    /**
     * Set Raw Request
     *
     * @param DataObject|null $request
     * @return DataObject
     */
    public function setPackageRequest(?DataObject $request): DataObject
    {
        $size = $request->getUspsSize() ?? $this->carrierModel->getConfigData('size');
        $request->setPackageSize($size);
        $height = $request->getHeight() ?? $this->carrierModel->getConfigData('height');
        $length = $request->getLength() ?? $this->carrierModel->getConfigData('length');
        $width = $request->getWidth() ?? $this->carrierModel->getConfigData('width');

        // Set default dimensions if not provided
        $defaultDimension = 1;
        $height = (int)($height ?: $defaultDimension);
        $length = (int)($length ?: $defaultDimension);
        $width = (int)($width ?: $defaultDimension);

        if ($size === 'LARGE') {
            $request->setPackageHeight($height);
            $request->setPackageLength($length);
            $request->setPackageWidth($width);

            // Handle girth for non-rectangular containers
            $container = $this->carrierModel->getConfigData('container');
            if (in_array($container, ['NONRECTANGULAR', 'VARIABLE'], true)) {
                $girth = (int)($request->getGirth()
                    ?: $this->carrierModel->getConfigData('girth') ?: $defaultDimension);
                $request->setPackageGirth($girth);
            }
        } else {
            $request->setPackageHeight($height);
            $request->setPackageWidth($width);
            $request->setPackageLength($length);
        }

        // Apply minimum dimensions if they exist for the shipping method
        $packageDimension = $this->shippingMethodManager->getMethodMinDimensions($request->getShippingMethod());
        if ($packageDimension) {
            $request->setPackageHeight(($packageDimension['height'] ?? $height));
            $request->setPackageWidth(($packageDimension['width'] ?? $width));
            $request->setPackageLength(($packageDimension['length'] ?? $length));
        }

        return $request;
    }

    /**
     * Handle error response and render message to user.
     *
     * @param array $response
     * @return array
     */
    public function handleErrorResponse(array $response): array
    {
        $errorMessage = [];
        if (isset($response['error'])) {
            $error = $response['error'];
            if (isset($error['errors']) && is_array($error['errors'])) {
                foreach ($error['errors'] as $errorDetail) {
                    $errorMessage[] = $errorDetail['detail'];
                }
            }
        }
        return $errorMessage;
    }

    /**
     * Convert decimal weight into pound-ounces format
     *
     * @param float $weightInPounds
     * @return float[]
     */
    public function _convertPoundOunces(float $weightInPounds): array
    {
        $weightInOunces = ceil($weightInPounds * Carrier::OUNCES_POUND);
        $pounds = floor($weightInOunces / Carrier::OUNCES_POUND);
        $ounces = $weightInOunces % Carrier::OUNCES_POUND;

        return [$pounds, $ounces];
    }

    /**
     * Parse zip from string to zip5-zip4
     *
     * @param string $zipString
     * @param bool $returnFull
     * @return string[]
     */
    public function _parseZip(string $zipString, bool $returnFull = false): array
    {
        $zip4 = '';
        $zip5 = '';
        $zip = [$zipString];
        if ($zipString !== null && preg_match('/[\\d\\w]{5}\\-[\\d\\w]{4}/', $zipString) != 0) {
            $zip = explode('-', $zipString);
        }
        $count = count($zip);
        for ($i = 0; $i < $count; ++$i) {
            if (strlen($zip[$i] ?? '') == 5) {
                $zip5 = $zip[$i];
            } elseif (strlen($zip[$i] ?? '') == 4) {
                $zip4 = $zip[$i];
            }
        }
        if (empty($zip5) && empty($zip4) && $returnFull) {
            $zip5 = $zipString;
        }

        return [$zip5, $zip4];
    }

    /**
     * Get allowed rest shipping methods
     *
     * @return array
     */
    public function getRestAllowedMethods(): array
    {
        $allowed = explode(',', $this->carrierModel->getConfigData('rest_allowed_methods') ?? '');
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->carrierModel->getCode('rest_method', $k);
        }

        return $arr;
    }

    /**
     * Prepare custom form request for International shipment label
     *
     * @param DataObject $request
     * @param DataObject $packageParams
     * @param array $requestParam
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareCustomFormForIntl($request, $packageParams, &$requestParam): void
    {
        $aesitn = $this->carrierModel->getConfigData('aesitn');
        $recipientCountryCode = $request->getRecipientAddressCountryCode();
        $packageItems = $request->getPackageItems();
        // get countries of manufacture
        $productCountriesManufacturesList = [];
        $productIds = [];
        foreach ($packageItems as $itemShipment) {
            $item = new DataObject();
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
            $productCountriesManufacturesList[$product->getId()] = $product->getCountryOfManufacture();
        }

        if ($packageParams->getContentType() == 'OTHER' && $packageParams->getContentTypeOther() != null) {
            $requestParam['customsForm']['customsContentType'] = $packageParams->getContentType();
        } else {
            $requestParam['customsForm']['customsContentType'] = $packageParams->getContentType();
        }

        foreach ($packageItems as $itemShipment) {
            $item = new DataObject();
            $item->setData($itemShipment);
            $itemWeight = $item->getWeight() * $item->getQty();
            if ($packageParams->getWeightUnits() != Weight::POUND) {
                $itemWeight = $this->_carrierHelper->convertMeasureWeight(
                    $itemWeight,
                    $packageParams->getWeightUnits(),
                    Weight::POUND
                );
            }
            $countryofOrigin = $productCountriesManufacturesList[$item->getProductId()]
                 ?? $request->getShipperAddressCountryCode();

            $ceiledQty = max(1, ceil((int)$item->getQty()));
            list($itemPoundsWeight) = $this->_convertPoundOunces((float)$itemWeight);
            $requestParam['customsForm']['contents'][] = [
                "itemDescription" => $item->getName(),
                "itemQuantity" => (int) $ceiledQty,
                "itemTotalWeight" => $itemPoundsWeight,
                "itemTotalValue" => (float) sprintf('%.2F', $item->getCustomsValue() * $item->getQty()),
                "countryofOrigin" => $countryofOrigin
            ];
        }

        if (empty($aesitn)) {
            if ($recipientCountryCode === 'CA') {
                $aesitn = 'NOEEI 30.36';
            } else {
                throw new LocalizedException(
                    __(
                        'Each type of goods in the shipment under certain value or less according to the
                        <a href="%1" target="_blank">Schedule B Export Codes</a>
                        and the shipment must not require an export license. If any item exceeds this value,
                        an export license is required. A shipment (regardless of value) is going to Canada and does not
                        require an export license. Users may enter <b>%2</b> in the AESITN field if the shipment value
                        meets the exemption criteria. Please contact USPS for more information.',
                        'www.census.gov/foreign-trade/schedules/b',
                        "'NO EEI 30.37(a)'"
                    )
                );
            }
        }
        $requestParam['customsForm']['AESITN'] = $aesitn;
    }

    /**
     * Prepare Package Description for International shipment label
     *
     * @param DataObject $request
     * @param DataObject $packageParams
     * @param array $requestParam
     * @return void
     * @throws LocalizedException
     */
    private function preparePackageDescForIntl($request, $packageParams, &$requestParam): void
    {
        $dimensions = $this->preparePackageDimensions($request, $packageParams);
        $girth = $dimensions['girth'] ?: $request->getPackageGirth();
        $packagePoundsWeight = $dimensions['weight'] ?: null;
        $shippingMethod = $request->getShippingMethod();

        $minDimension = $this->shippingMethodManager->getMethodMinDimensions($shippingMethod);
        $maxDimension = $this->shippingMethodManager->getMethodMaxDimensions($shippingMethod);

        $dimensionNames = ['length', 'width', 'height'];

        foreach ($dimensionNames as $dimensionName) {
            $dimensionValue = $dimensions[$dimensionName];
            if (isset($minDimension[$dimensionName]) && $dimensionValue < $minDimension[$dimensionName]) {
                throw new LocalizedException(__(
                    'The package dimensions are invalid. The package %1 must be greater than or equal to %2 inch.',
                    $dimensionName,
                    $minDimension[$dimensionName]
                ));
            }

            if (isset($maxDimension[$dimensionName]) && $dimensionValue > $maxDimension[$dimensionName]) {
                throw new LocalizedException(__(
                    'The package dimensions are invalid. The package %1 must be less than or equal to %2 inch.',
                    $dimensionName,
                    $maxDimension[$dimensionName]
                ));
            }
        }

        $requestParam['packageDescription'] = [
            "destinationEntryFacilityType" =>
                $this->shippingMethodManager->getMethodDestinationEntryFacilityType($shippingMethod),
            "height" => (float)$dimensions['height'],
            "length" => (float)$dimensions['length'],
            "width" => (float)$dimensions['width'],
            "weight" => (float) $packagePoundsWeight,
            "mailClass" => $this->shippingMethodManager->getMethodMailClass($shippingMethod),
            "mailingDate" => (new \DateTime())->format('Y-m-d'),
            "processingCategory" => $this->shippingMethodManager->getMethodProcessingCategory($shippingMethod),
            "rateIndicator" => $this->shippingMethodManager->getRateIndicator($shippingMethod),
        ];

        if ($girth > 0) {
            $requestParam['packageDescription']['girth'] = $girth;
        }
    }

    /**
     * To receive payment access token for label generation
     *
     * @param string $accessToken
     * @return string
     * @throws LocalizedException
     * @throws \Throwable
     */
    public function getLabelPaymentTokenRequest(string $accessToken): string
    {
        $cridNumber = $this->carrierModel->getConfigData('crid');
        $midNumber = $this->carrierModel->getConfigData('mid');
        $manifestMID = $this->carrierModel->getConfigData('mmid');
        $accountNumber = $this->carrierModel->getConfigData('account_number');
        $accountType = $this->carrierModel->getConfigData('account_type');
        $accountInfo = [
            "CRID" => $cridNumber,
            "MID" => $midNumber,
            "manifestMID" => $manifestMID,
            "accountNumber" => $accountNumber,
            "accountType" => $accountType
        ];

        $authUrl = $this->carrierModel->getUrl(self::PAYMENT_AUTH_REQUEST_END_POINT);
        return $this->uspsPaymentAuthToken->getPaymentAuthToken($accessToken, $authUrl, $accountInfo);
    }

    /**
     * Prepares package dimensions and weight for shipment.
     *
     * This method calculates and converts the dimensions and weight of a package
     * into the required units (inches and pounds) if they are not already in those units.
     * It also handles the conversion of girth dimensions if necessary.
     *
     * @param DataObject $request The shipment request object containing package details.
     * @param DataObject $packageParams The package parameters object containing dimensions and weight units.
     * @return array An associative array containing the converted dimensions and weight:
     *               - 'height': The height of the package in inches.
     *               - 'width': The width of the package in inches.
     *               - 'length': The length of the package in inches.
     *               - 'girth': The girth of the package in inches.
     *               - 'weight': The weight of the package in pounds.
     * @throws LocalizedException If a conversion error occurs.
     */
    public function preparePackageDimensions(DataObject $request, DataObject $packageParams): array
    {
        try {
            // Retrieve initial dimensions and weight from the package parameters
            // Use request values as fallback when packageParams doesn't have values
            $height = $packageParams->getHeight() ?: $request->getPackageHeight();
            $width = $packageParams->getWidth() ?: $request->getPackageWidth();
            $length = $packageParams->getLength() ?: $request->getPackageLength();
            $girth = $packageParams->getGirth() ?: $request->getPackageGirth();
            $packageWeight = $request->getPackageWeight();

            // Convert weight to pounds if it is not already in pounds
            if ($packageParams->getWeightUnits() != Weight::POUND) {
                $packageWeight = $this->_carrierHelper->convertMeasureWeight(
                    (float)$request->getPackageWeight(),
                    $packageParams->getWeightUnits(),
                    Weight::POUND
                );
            }

            // Convert dimensions to inches if they are not already in inches
            if ($packageParams->getDimensionUnits() != Length::INCH) {
                $length = round(
                    (float) $this->_carrierHelper->convertMeasureDimension(
                        (float)$length,
                        $packageParams->getDimensionUnits(),
                        Length::INCH
                    )
                );
                $width = round(
                    (float) $this->_carrierHelper->convertMeasureDimension(
                        (float)$width,
                        $packageParams->getDimensionUnits(),
                        Length::INCH
                    )
                );
                $height = round(
                    (float) $this->_carrierHelper->convertMeasureDimension(
                        (float)$height,
                        $packageParams->getDimensionUnits(),
                        Length::INCH
                    )
                );
            }

            // Convert girth to inches if it is not already in inches
            if ($packageParams->getGirthDimensionUnits() != Length::INCH) {
                $girth = round(
                    (float) $this->_carrierHelper->convertMeasureDimension(
                        (float)$girth,
                        $packageParams->getGirthDimensionUnits(),
                        Length::INCH
                    )
                );
            }

            // Return the converted dimensions and weight as an associative array
            return [
                'height' => $height,
                'width' => $width,
                'length' => $length,
                'girth' => $girth,
                'weight' => $packageWeight,
            ];
        } catch (MeasureException $e) {
            $this->_logger->error('Error converting package dimensions or weight: ' . $e->getMessage());
            throw new LocalizedException(__('Failed to prepare package dimensions. Please check the input values.'));
        } catch (Exception $e) {
            $this->_logger->critical('Unexpected error: ' . $e->getMessage());
            throw new LocalizedException(__('An unexpected error occurred while preparing package dimensions.'));
        }
    }
}
