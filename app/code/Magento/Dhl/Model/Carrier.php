<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Dhl\Model;

use Exception;
use Laminas\Http\Request as HttpRequest;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Dhl\Model\Validator\XmlValidator;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Async\CallbackDeferred;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Math\Division;
use Magento\Framework\Measure\Length;
use Magento\Framework\Measure\Weight;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\Result\ProxyDeferredFactory;
use Magento\Shipping\Model\Shipment\Request as ShipmentRequest;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Throwable;
use const DATE_RFC3339;

/**
 * DHL International (API v1.4)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractDhl implements CarrierInterface
{
    /**#@+
     * Carrier Product indicator
     */
    public const DHL_CONTENT_TYPE_DOC = 'D';
    public const DHL_CONTENT_TYPE_NON_DOC = 'N';
    /**#@-*/

    /**#@+
     * Minimum allowed values for shipping package dimensions
     */
    public const DIMENSION_MIN_CM = 3;
    public const DIMENSION_MIN_IN = 1;
    /**#@-*/

    /**
     * Config path to UE country list
     */
    public const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * Container types that could be customized
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = [self::DHL_CONTENT_TYPE_NON_DOC, self::DHL_CONTENT_TYPE_DOC];

    /**
     * Code of the carrier
     */
    public const CODE = 'dhl';

    /**
     * DHL service prefixes used for message reference
     */
    private const SERVICE_PREFIX_QUOTE = 'QUOT';
    private const SERVICE_PREFIX_SHIPVAL = 'SHIP';
    private const SERVICE_PREFIX_TRACKING = 'TRCK';

    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $_request;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result;

    /**
     * Countries parameters data
     *
     * @var Element|null
     */
    protected $_countryParams;

    /**
     * Errors placeholder
     *
     * @var string[]
     */
    protected $_errors = [];

    /**
     * Dhl rates result
     *
     * @var array
     */
    protected $_rates = [];

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Free Method config path
     *
     * @var string
     */
    protected $_freeMethod = 'free_method_nondoc';

    /**
     * Max weight without fee
     *
     * @var int
     */
    protected $_maxWeight = 70;

    /**
     * Flag if response is for shipping label creating
     *
     * @var bool
     */
    protected $_isShippingLabelFlag = false;

    /**
     * Request variables array
     *
     * @var array
     */
    protected $_requestVariables = [
        'id' => ['code' => 'dhl_id', 'setCode' => 'id'],
        'password' => ['code' => 'dhl_password', 'setCode' => 'password'],
        'account' => ['code' => 'dhl_account', 'setCode' => 'account_nbr'],
        'shipping_key' => ['code' => 'dhl_shipping_key', 'setCode' => 'shipping_key'],
        'shipping_intlkey' => ['code' => 'dhl_shipping_intl_key', 'setCode' => 'shipping_intl_key'],
        'shipment_type' => ['code' => 'dhl_shipment_type', 'setCode' => 'shipment_type'],
        'dutiable' => ['code' => 'dhl_dutiable', 'setCode' => 'dutiable'],
        'dutypaymenttype' => ['code' => 'dhl_duty_payment_type', 'setCode' => 'duty_payment_type'],
        'contentdesc' => ['code' => 'dhl_content_desc', 'setCode' => 'content_desc'],
    ];

    /**
     * Flag that shows if shipping is domestic
     *
     * @var bool
     */
    protected $_isDomestic = false;

    /**
     * Core string
     *
     * @var StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $_carrierHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Reader
     */
    protected $_configReader;

    /**
     * @var Division
     */
    protected $mathDivision;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var LaminasClientFactory
     * phpcs:ignore Magento2.Commenting.ClassAndInterfacePHPDocFormatting
     * @deprecated Use asynchronous client.
     * @see $httpClient
     */
    protected $_httpClientFactory;

    /**
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = [
        'SiteID', 'Password'
    ];

    /**
     * Xml response validator
     *
     * @var XmlValidator
     */
    private $xmlValidator;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var AsyncClientInterface
     */
    private $httpClient;

    /**
     * @var ProxyDeferredFactory
     */
    private $proxyDeferredFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param MethodFactory $rateMethodFactory
     * @param ResultFactory $trackFactory
     * @param ErrorFactory $trackErrorFactory
     * @param StatusFactory $trackStatusFactory
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param CurrencyFactory $currencyFactory
     * @param Data $directoryData
     * @param StockRegistryInterface $stockRegistry
     * @param \Magento\Shipping\Helper\Carrier $carrierHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param Reader $configReader
     * @param StoreManagerInterface $storeManager
     * @param StringUtils $string
     * @param Division $mathDivision
     * @param ReadFactory $readFactory
     * @param DateTime $dateTime
     * @param LaminasClientFactory $httpClientFactory
     * @param array $data
     * @param XmlValidator|null $xmlValidator
     * @param ProductMetadataInterface|null $productMetadata
     * @param AsyncClientInterface|null $httpClient
     * @param ProxyDeferredFactory|null $proxyDeferredFactory
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
        \Magento\Shipping\Helper\Carrier $carrierHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        Reader $configReader,
        StoreManagerInterface $storeManager,
        StringUtils $string,
        Division $mathDivision,
        ReadFactory $readFactory,
        DateTime $dateTime,
        LaminasClientFactory $httpClientFactory,
        array $data = [],
        XmlValidator $xmlValidator = null,
        ProductMetadataInterface $productMetadata = null,
        ?AsyncClientInterface $httpClient = null,
        ?ProxyDeferredFactory $proxyDeferredFactory = null
    ) {
        $this->readFactory = $readFactory;
        $this->_carrierHelper = $carrierHelper;
        $this->_coreDate = $coreDate;
        $this->_storeManager = $storeManager;
        $this->_configReader = $configReader;
        $this->string = $string;
        $this->mathDivision = $mathDivision;
        $this->_dateTime = $dateTime;
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
        if ($this->getConfigData('content_type') == self::DHL_CONTENT_TYPE_DOC) {
            $this->_freeMethod = 'free_method_doc';
        }
        $this->xmlValidator = $xmlValidator ?? ObjectManager::getInstance()->get(XmlValidator::class);
        $this->productMetadata = $productMetadata ?? ObjectManager::getInstance()->get(ProductMetadataInterface::class);
        $this->httpClient = $httpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
        $this->proxyDeferredFactory = $proxyDeferredFactory
            ?? ObjectManager::getInstance()->get(ProxyDeferredFactory::class);
    }

    /**
     * Returns value of given variable
     *
     * @param string|int $origValue
     * @param string $pathToValue
     * @return string|int|null
     */
    protected function _getDefaultValue($origValue, $pathToValue)
    {
        if (!$origValue) {
            $origValue = $this->_scopeConfig->getValue(
                $pathToValue,
                ScopeInterface::SCOPE_STORE,
                $this->getStore()
            );
        }

        return $origValue;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return bool|Result|Error
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $requestDhl = clone $request;
        $this->setStore($requestDhl->getStoreId());
        $origCompanyName = $this->_getDefaultValue(
            $requestDhl->getOrigCompanyName(),
            Information::XML_PATH_STORE_INFO_NAME
        );
        $origCountryId = $this->_getDefaultValue($requestDhl->getOrigCountryId(), Shipment::XML_PATH_STORE_COUNTRY_ID);
        $origState = $this->_getDefaultValue($requestDhl->getOrigState(), Shipment::XML_PATH_STORE_REGION_ID);
        $origCity = $this->_getDefaultValue($requestDhl->getOrigCity(), Shipment::XML_PATH_STORE_CITY);
        $origPostcode = $this->_getDefaultValue($requestDhl->getOrigPostcode(), Shipment::XML_PATH_STORE_ZIP);
        $requestDhl->setOrigCompanyName($origCompanyName)
            ->setCountryId($origCountryId)
            ->setOrigState($origState)
            ->setOrigCity($origCity)
            ->setOrigPostal($origPostcode);
        $this->setRequest($requestDhl);
        //Loading quotes
        //Saving $result to use proper result with the callback
        $this->_result = $result = $this->_getQuotes();
        //After quotes are loaded parsing the response.
        return $this->proxyDeferredFactory->create(
            [
                'deferred' => new CallbackDeferred(
                    function () use ($request, $result) {
                        $this->_result = $result;
                        $this->_updateFreeMethodQuote($request);

                        return $this->_result;
                    }
                )
            ]
        );
    }

    /**
     * Set Free Method Request
     *
     * @param string $freeMethod
     * @return void
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        $this->_rawRequest->setFreeMethodRequest(true);
        $freeWeight = $this->getTotalNumOfBoxes($this->_rawRequest->getFreeMethodWeight());
        $this->_rawRequest->setWeight($freeWeight);
        $this->_rawRequest->setService($freeMethod);
    }

    /**
     * Returns request result
     *
     * @return Result|null
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Fills request object with Dhl config parameters
     *
     * @param DataObject $requestObject
     * @return DataObject
     */
    protected function _addParams(DataObject $requestObject)
    {
        foreach ($this->_requestVariables as $code => $objectCode) {
            if ($this->_request->getDhlId()) {
                $value = $this->_request->getData($objectCode['code']);
            } else {
                $value = $this->getConfigData($code);
            }
            $requestObject->setData($objectCode['setCode'], $value);
        }

        return $requestObject;
    }

    /**
     * Prepare and set request in property of current instance
     *
     * @param DataObject $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRequest(DataObject $request)
    {
        $this->_request = $request;
        $this->setStore($request->getStoreId());

        $requestObject = new DataObject();

        $requestObject->setIsGenerateLabelReturn($request->getIsGenerateLabelReturn());

        $requestObject->setStoreId($request->getStoreId());

        if ($request->getLimitMethod()) {
            $requestObject->setService($request->getLimitMethod());
        }

        $requestObject = $this->_addParams($requestObject);

        if ($request->getDestPostcode()) {
            $requestObject->setDestPostal($request->getDestPostcode());
        }

        $requestObject->setOrigCountry(
            $this->_getDefaultValue($request->getOrigCountry(), Shipment::XML_PATH_STORE_COUNTRY_ID)
        )->setOrigCountryId(
            $this->_getDefaultValue($request->getOrigCountryId(), Shipment::XML_PATH_STORE_COUNTRY_ID)
        );

        $shippingWeight = $request->getPackageWeight();
        $destStreet = $request->getDestStreet() !== null ? str_replace("\n", '', $request->getDestStreet()) : '';

        $requestObject->setValue(sprintf('%.2f', $request->getPackageValue()))
            ->setValueWithDiscount($request->getPackageValueWithDiscount())
            ->setCustomsValue($request->getPackageCustomsValue())
            ->setDestStreet($this->string->substr($destStreet, 0, 35))
            ->setDestStreetLine2($request->getDestStreetLine2())
            ->setDestCity($request->getDestCity())
            ->setOrigCompanyName($request->getOrigCompanyName())
            ->setOrigCity($request->getOrigCity())
            ->setOrigPhoneNumber($request->getOrigPhoneNumber())
            ->setOrigPersonName($request->getOrigPersonName())
            ->setOrigEmail(
                $this->_scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    ScopeInterface::SCOPE_STORE,
                    $requestObject->getStoreId()
                )
            )
            ->setOrigCity($request->getOrigCity())
            ->setOrigPostal($request->getOrigPostal())
            ->setOrigStreetLine2($request->getOrigStreetLine2())
            ->setDestPhoneNumber($request->getDestPhoneNumber())
            ->setDestPersonName($request->getDestPersonName())
            ->setDestCompanyName($request->getDestCompanyName());

        $originStreet2 = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS2,
            ScopeInterface::SCOPE_STORE,
            $requestObject->getStoreId()
        );

        $requestObject->setOrigStreet($request->getOrigStreet() ? $request->getOrigStreet() : $originStreet2);

        if (is_numeric($request->getOrigState())) {
            $requestObject->setOrigState($this->_regionFactory->create()->load($request->getOrigState())->getCode());
        } else {
            $requestObject->setOrigState($request->getOrigState());
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        // for DHL, Puerto Rico state for US will assume as Puerto Rico country
        // for Puerto Rico, dhl will ship as international
        if ($destCountry == self::USA_COUNTRY_ID
            && ($request->getDestPostcode() == '00912' || $request->getDestRegionCode() == self::PUERTORICO_COUNTRY_ID)
        ) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        $requestObject->setDestCountryId($destCountry)
            ->setDestState($request->getDestRegionCode())
            ->setWeight($shippingWeight)
            ->setFreeMethodWeight($request->getFreeMethodWeight())
            ->setOrderShipment($request->getOrderShipment());

        if ($request->getPackageId()) {
            $requestObject->setPackageId($request->getPackageId());
        }

        $requestObject->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->setRawRequest($requestObject);

        return $this;
    }

    /**
     * Get allowed shipping methods
     *
     * @return string[]
     * @throws LocalizedException
     */
    public function getAllowedMethods()
    {
        $contentType = $this->getConfigData('content_type');

        if ($this->_isDomestic) {
            $allowedMethods = array_merge(
                explode(',', $this->getConfigData('doc_methods') ?? ''),
                explode(',', $this->getConfigData('nondoc_methods') ?? '')
            );
        } else {
            switch ($contentType) {
                case self::DHL_CONTENT_TYPE_DOC:
                    $allowedMethods = explode(',', $this->getConfigData('doc_methods') ?? '');
                    break;
                case self::DHL_CONTENT_TYPE_NON_DOC:
                    $allowedMethods = explode(',', $this->getConfigData('nondoc_methods') ?? '');
                    break;
                default:
                    throw new LocalizedException(__('Wrong Content Type'));
            }
        }
        $methods = [];
        foreach ($allowedMethods as $method) {
            $methods[$method] = $this->getDhlProductTitle($method);
        }

        return $methods;
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'unit_of_measure' => ['L' => __('Pounds'), 'K' => __('Kilograms')],
            'unit_of_dimension' => ['I' => __('Inches'), 'C' => __('Centimeters')],
            'unit_of_dimension_cut' => ['I' => __('inch'), 'C' => __('cm')],
            'dimensions' => ['HEIGHT' => __('Height'), 'DEPTH' => __('Depth'), 'WIDTH' => __('Width')],
            'size' => ['0' => __('Regular'), '1' => __('Specific')],
            'dimensions_variables' => [
                'L' => Weight::POUND,
                'LB' => Weight::POUND,
                'POUND' => Weight::POUND,
                'K' => Weight::KILOGRAM,
                'KG' => Weight::KILOGRAM,
                'KILOGRAM' => Weight::KILOGRAM,
                'I' => Length::INCH,
                'IN' => Length::INCH,
                'INCH' => Length::INCH,
                'C' => Length::CENTIMETER,
                'CM' => Length::CENTIMETER,
                'CENTIMETER' => Length::CENTIMETER,
            ],
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        $code = strtoupper($code);
        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Returns DHL shipment methods (depending on package content type, if necessary)
     *
     * @param string $doc Package content type (doc/non-doc) see DHL_CONTENT_TYPE_* constants
     * @return array
     */
    public function getDhlProducts($doc)
    {
        $docType = [
            '2' => __('Easy shop'),
            '5' => __('Sprintline'),
            '6' => __('Secureline'),
            '7' => __('Express easy'),
            '9' => __('Europack'),
            'B' => __('Break bulk express'),
            'C' => __('Medical express'),
            'D' => __('Express worldwide'),
            'U' => __('Express worldwide'),
            'K' => __('Express 9:00'),
            'L' => __('Express 10:30'),
            'G' => __('Domestic economy select'),
            'W' => __('Economy select'),
            'I' => __('Domestic express 9:00'),
            'N' => __('Domestic express'),
            'O' => __('Others'),
            'R' => __('Globalmail business'),
            'S' => __('Same day'),
            'T' => __('Express 12:00'),
            'X' => __('Express envelope'),
        ];

        $nonDocType = [
            '1' => __('Domestic express 12:00'),
            '3' => __('Easy shop'),
            '4' => __('Jetline'),
            '8' => __('Express easy'),
            'P' => __('Express worldwide'),
            'Q' => __('Medical express'),
            'E' => __('Express 9:00'),
            'F' => __('Freight worldwide'),
            'H' => __('Economy select'),
            'J' => __('Jumbo box'),
            'M' => __('Express 10:30'),
            'N' => __('Domestic express'),
            'V' => __('Europack'),
            'Y' => __('Express 12:00'),
        ];

        if ($this->_isDomestic) {
            return $docType + $nonDocType;
        }
        if ($doc == self::DHL_CONTENT_TYPE_DOC) {
            // Documents shipping
            return $docType;
        } else {
            // Services for shipping non-documents cargo
            return $nonDocType;
        }
    }

    /**
     * Returns title of DHL shipping method by its code
     *
     * @param string $code One-symbol code (see getDhlProducts())
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDhlProductTitle($code)
    {
        $contentType = $this->getConfigData('content_type');
        $dhlProducts = $this->getDhlProducts($contentType);

        return $dhlProducts[$code] ?? false;
    }

    /**
     * Convert item weight to needed weight based on config weight unit dimensions
     *
     * @param float $weight
     * @param bool $maxWeight
     * @param string|bool $configWeightUnit
     * @return float
     */
    protected function _getWeight($weight, $maxWeight = false, $configWeightUnit = false)
    {
        if ($maxWeight) {
            $configWeightUnit = Weight::KILOGRAM;
        } elseif ($configWeightUnit) {
            $configWeightUnit = $this->getCode('dimensions_variables', $configWeightUnit);
        } else {
            $configWeightUnit = $this->getCode(
                'dimensions_variables',
                (string)$this->getConfigData('unit_of_measure')
            );
        }

        $countryWeightUnit = $this->getCode('dimensions_variables', $this->_getWeightUnit());

        if ($configWeightUnit != $countryWeightUnit) {
            $weight = $this->_carrierHelper->convertMeasureWeight(
                (float)$weight,
                $configWeightUnit,
                $countryWeightUnit
            );
        }

        return sprintf('%.3f', $weight);
    }

    /**
     * Prepare items to pieces
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function _getAllItems()
    {
        $allItems = $this->_request->getAllItems();
        $fullItems = [];

        foreach ($allItems as $item) {
            if ($item->getProductType() == Type::TYPE_BUNDLE && $item->getProduct()->getShipmentType()) {
                continue;
            }

            $qty = $item->getQty();
            $changeQty = true;
            $checkWeight = true;
            $decimalItems = [];

            if ($item->getParentItem()) {
                if (!$item->getParentItem()->getProduct()->getShipmentType()) {
                    continue;
                }
                if ($item->getIsQtyDecimal()) {
                    $qty = $item->getParentItem()->getQty();
                } else {
                    $qty = $item->getParentItem()->getQty() * $item->getQty();
                }
            }

            $itemWeight = $item->getWeight();
            if ($item->getIsQtyDecimal() && $item->getProductType() != Type::TYPE_BUNDLE) {
                $productId = $item->getProduct()->getId();
                $stockItemDo = $this->stockRegistry->getStockItem($productId, $item->getStore()->getWebsiteId());
                $isDecimalDivided = $stockItemDo->getIsDecimalDivided();
                if ($isDecimalDivided) {
                    if ($stockItemDo->getEnableQtyIncrements()
                        && $stockItemDo->getQtyIncrements()
                    ) {
                        $itemWeight = $itemWeight * $stockItemDo->getQtyIncrements();
                        $qty = round($item->getWeight() / $itemWeight * $qty);
                        $changeQty = false;
                    } else {
                        $itemWeight = $this->_getWeight($itemWeight * $item->getQty());
                        $maxWeight = $this->_getWeight($this->_maxWeight, true);
                        if ($itemWeight > $maxWeight) {
                            $qtyItem = floor($itemWeight / $maxWeight);
                            $decimalItems[] = ['weight' => $maxWeight, 'qty' => $qtyItem];
                            $weightItem = $this->mathDivision->getExactDivision($itemWeight, $maxWeight);
                            if ($weightItem) {
                                $decimalItems[] = ['weight' => $weightItem, 'qty' => 1];
                            }
                            $checkWeight = false;
                        }
                    }
                } else {
                    $itemWeight = $itemWeight * $item->getQty();
                }
            }

            if ($checkWeight && $this->_getWeight($itemWeight) > $this->_getWeight($this->_maxWeight, true)) {
                return [];
            }

            if ($changeQty
                && !$item->getParentItem()
                && $item->getIsQtyDecimal()
                && $item->getProductType() != Type::TYPE_BUNDLE
            ) {
                $qty = 1;
            }

            if (!empty($decimalItems)) {
                foreach ($decimalItems as $decimalItem) {
                    $fullItems[] = array_fill(0, $decimalItem['qty'] * $qty, $decimalItem['weight']);
                }
            } else {
                $fullItems[] = array_fill(0, $qty, $this->_getWeight($itemWeight));
            }
        }

        $fullItems = array_merge([], ...$fullItems);
        sort($fullItems);

        return $fullItems;
    }
    //phpcs:enable

    /**
     * Make pieces
     *
     * @param Element $nodeBkgDetails
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _makePieces(Element $nodeBkgDetails)
    {
        $divideOrderWeight = (string)$this->getConfigData('divide_order_weight');
        $nodePieces = $nodeBkgDetails->addChild('Pieces', '', '');
        $items = $this->_getAllItems();
        $numberOfPieces = 0;

        if ($divideOrderWeight && !empty($items)) {
            $maxWeight = $this->_getWeight($this->_maxWeight, true);
            $sumWeight = 0;

            $reverseOrderItems = $items;
            arsort($reverseOrderItems);

            foreach ($reverseOrderItems as $key => $weight) {
                if (!isset($items[$key])) {
                    continue;
                }
                unset($items[$key]);
                $sumWeight = $weight;
                foreach ($items as $key => $weight) {
                    if ($sumWeight + $weight < $maxWeight) {
                        unset($items[$key]);
                        $sumWeight += $weight;
                    } elseif ($sumWeight + $weight > $maxWeight) {
                        $numberOfPieces++;
                        $nodePiece = $nodePieces->addChild('Piece', '', '');
                        $nodePiece->addChild('PieceID', $numberOfPieces);
                        $this->_addDimension($nodePiece);
                        $nodePiece->addChild('Weight', sprintf('%.3f', $sumWeight));
                        break;
                    } else {
                        unset($items[$key]);
                        $numberOfPieces++;
                        $sumWeight += $weight;
                        $nodePiece = $nodePieces->addChild('Piece', '', '');
                        $nodePiece->addChild('PieceID', $numberOfPieces);
                        $this->_addDimension($nodePiece);
                        $nodePiece->addChild('Weight', sprintf('%.3f', $sumWeight));
                        $sumWeight = 0;
                        break;
                    }
                }
            }
            if ($sumWeight > 0) {
                $numberOfPieces++;
                $nodePiece = $nodePieces->addChild('Piece', '', '');
                $nodePiece->addChild('PieceID', $numberOfPieces);
                $this->_addDimension($nodePiece);
                $nodePiece->addChild('Weight', sprintf('%.3f', $sumWeight));
            }
        } else {
            $nodePiece = $nodePieces->addChild('Piece', '', '');
            $nodePiece->addChild('PieceID', 1);
            $this->_addDimension($nodePiece);
            $nodePiece->addChild('Weight', $this->_getWeight($this->_rawRequest->getWeight()));
        }

        $handlingAction = $this->getConfigData('handling_action');
        if ($handlingAction == AbstractCarrier::HANDLING_ACTION_PERORDER || !$numberOfPieces) {
            $numberOfPieces = 1;
        }
        $this->_numBoxes = $numberOfPieces;
    }

    /**
     * Convert item dimension to needed dimension based on config dimension unit of measure
     *
     * @param float $dimension
     * @param string|bool $configWeightUnit
     * @return float
     */
    protected function _getDimension($dimension, $configWeightUnit = false)
    {
        if (!$configWeightUnit) {
            $configWeightUnit = $this->getCode(
                'dimensions_variables',
                (string)$this->getConfigData('unit_of_measure')
            );
        } else {
            $configWeightUnit = $this->getCode('dimensions_variables', $configWeightUnit);
        }

        if ($configWeightUnit == Weight::POUND) {
            $configDimensionUnit = Length::INCH;
        } else {
            $configDimensionUnit = Length::CENTIMETER;
        }

        $countryDimensionUnit = $this->getCode('dimensions_variables', $this->_getDimensionUnit());

        if ($configDimensionUnit != $countryDimensionUnit) {
            $dimension = $this->_carrierHelper->convertMeasureDimension(
                (float)$dimension,
                $configDimensionUnit,
                $countryDimensionUnit
            );
        }

        return round((float) $dimension, 3);
    }

    /**
     * Add dimension to piece
     *
     * @param Element $nodePiece
     * @return void
     */
    protected function _addDimension($nodePiece)
    {
        $sizeChecker = (string)$this->getConfigData('size');

        $height = $this->_getDimension((float)$this->getConfigData('height'));
        $depth = $this->_getDimension((float)$this->getConfigData('depth'));
        $width = $this->_getDimension((float)$this->getConfigData('width'));

        if ($sizeChecker && $height && $depth && $width) {
            $nodePiece->addChild('Height', $height);
            $nodePiece->addChild('Depth', $depth);
            $nodePiece->addChild('Width', $width);
        }
    }

    /**
     * Process response received from DHL's API for quotes.
     *
     * @param array $responsesData
     * @return Error|Result
     */
    private function processQuotesResponses(array $responsesData)
    {
        usort(
            $responsesData,
            function (array $a, array $b): int {
                return $a['date'] <=> $b['date'];
            }
        );
        /** @var string $lastResponse */
        $lastResponse = '';
        //Processing different dates
        foreach ($responsesData as $responseData) {
            $debugPoint = [];
            $debugPoint['request'] = $this->filterDebugData($responseData['request']);
            $debugPoint['response'] = $this->filterDebugData($responseData['body']);
            $debugPoint['from_cache'] = $responseData['from_cache'];
            $unavailable = false;
            try {
                //Getting availability
                $bodyXml = $this->_xmlElFactory->create(['data' => $responseData['body']]);
                $code = $bodyXml->xpath('//GetQuoteResponse/Note/Condition/ConditionCode');
                if (isset($code[0]) && (int)$code[0] == self::CONDITION_CODE_SERVICE_DATE_UNAVAILABLE) {
                    $debugPoint['info'] = sprintf(
                        __("DHL service is not available at %s date")->render(),
                        $responseData['date']
                    );
                    $unavailable = true;
                }
            } catch (Throwable $exception) {
                //Failed to read response
                $unavailable = true;
                $this->_errors[$exception->getCode()] = $exception->getMessage();
            }
            if ($unavailable) {
                //Cannot get rates.
                $this->_debug($debugPoint);
                break;
            }
            //Caching rates
            $this->_setCachedQuotes($responseData['request'], $responseData['body']);
            $this->_debug($debugPoint);
            //Will only process rates available for the latest date possible.
            $lastResponse = $responseData['body'];
        }

        return $this->_parseResponse($lastResponse);
    }

    /**
     * Get shipping quotes
     *
     * @return AbstractModel|Result
     */
    protected function _getQuotes()
    {
        $responseBodies = [];
        /** @var HttpResponseDeferredInterface[][] $deferredResponses */
        $deferredResponses = [];
        $requestXml = $this->_buildQuotesRequestXml();
        for ($offset = 0; $offset <= self::UNAVAILABLE_DATE_LOOK_FORWARD; $offset++) {
            $date = date(self::REQUEST_DATE_FORMAT, strtotime($this->_getShipDate() . " +{$offset} days"));
            $this->_setQuotesRequestXmlDate($requestXml, $date);
            $request = $requestXml->asXML();
            $responseBody = $this->_getCachedQuotes($request);

            if ($responseBody === null) {
                $deferredResponses[] = [
                    'deferred' => $this->httpClient->request(
                        new Request(
                            (string)$this->getConfigData('gateway_url'),
                            Request::METHOD_POST,
                            ['Content-Type' => 'application/xml'],
                            mb_convert_encoding($request, 'UTF-8')
                        )
                    ),
                    'date' => $date,
                    'request' => $request
                ];
            } else {
                $responseBodies[] = [
                    'body' => $responseBody,
                    'date' => $date,
                    'request' => $request,
                    'from_cache' => true
                ];
            }
        }

        return $this->proxyDeferredFactory->create(
            [
                'deferred' => new CallbackDeferred(
                    function () use ($deferredResponses, $responseBodies) {
                        //Loading rates not found in cache
                        foreach ($deferredResponses as $deferredResponseData) {
                            $responseResult = null;
                            try {
                                $responseResult = $deferredResponseData['deferred']->get();
                            } catch (HttpException $exception) {
                                $this->_logger->critical($exception);
                            }
                            $responseBody = $responseResult ? $responseResult->getBody() : '';
                            $responseBodies[] = [
                                'body' => $responseBody,
                                'date' => $deferredResponseData['date'],
                                'request' => $deferredResponseData['request'],
                                'from_cache' => false
                            ];
                        }

                        return $this->processQuotesResponses($responseBodies);
                    }
                )
            ]
        );
    }

    /**
     * Get shipping quotes from DHL service
     *
     * @param string $request
     * @return string
     * @deprecated 100.3.3 Use asynchronous client.
     * @see _getQuotes()
     */
    protected function _getQuotesFromServer($request)
    {
        /** @var LaminasClient $client */
        $client = $this->_httpClientFactory->create();
        $client->setUri($this->getGatewayURL());
        $client->setOptions(['maxredirects' => 0, 'timeout' => 30]);
        $client->setRawBody(mb_convert_encoding($request, 'UTF-8'));
        $client->setMethod(HttpRequest::METHOD_POST);

        return $client->send()->getBody();
    }

    /**
     * Build quotes request XML object
     *
     * @return SimpleXMLElement
     */
    protected function _buildQuotesRequestXml()
    {
        $rawRequest = $this->_rawRequest;

        $xmlStr = '<?xml version="1.0" encoding = "UTF-8"?>' .
            '<req:DCTRequest schemaVersion="2.0" ' .
            'xmlns:req="http://www.dhl.com" ' .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xsi:schemaLocation="http://www.dhl.com DCT-req_global-2.0.xsd"/>';

        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);
        $nodeGetQuote = $xml->addChild('GetQuote', '', '');
        $nodeRequest = $nodeGetQuote->addChild('Request');

        $nodeServiceHeader = $nodeRequest->addChild('ServiceHeader');
        $nodeServiceHeader->addChild('MessageTime', $this->buildMessageTimestamp());
        $nodeServiceHeader->addChild(
            'MessageReference',
            $this->buildMessageReference(self::SERVICE_PREFIX_QUOTE)
        );
        $nodeServiceHeader->addChild('SiteID', (string) $this->getConfigData('id'));
        $nodeServiceHeader->addChild('Password', (string) $this->getConfigData('password'));

        $nodeMetaData = $nodeRequest->addChild('MetaData');
        $nodeMetaData->addChild('SoftwareName', $this->buildSoftwareName());
        $nodeMetaData->addChild('SoftwareVersion', $this->buildSoftwareVersion());

        $nodeFrom = $nodeGetQuote->addChild('From');
        $nodeFrom->addChild('CountryCode', $rawRequest->getOrigCountryId());
        $nodeFrom->addChild('Postalcode', $rawRequest->getOrigPostal());
        $nodeFrom->addChild('City', $rawRequest->getOrigCity());

        $nodeBkgDetails = $nodeGetQuote->addChild('BkgDetails');
        $nodeBkgDetails->addChild('PaymentCountryCode', $rawRequest->getOrigCountryId());
        $nodeBkgDetails->addChild(
            'Date',
            (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT)
        );
        $nodeBkgDetails->addChild('ReadyTime', 'PT' . (int)(string)$this->getConfigData('ready_time') . 'H00M');

        $nodeBkgDetails->addChild('DimensionUnit', $this->_getDimensionUnit());
        $nodeBkgDetails->addChild('WeightUnit', $this->_getWeightUnit());

        $this->_makePieces($nodeBkgDetails);

        $nodeBkgDetails->addChild('PaymentAccountNumber', (string)$this->getConfigData('account'));

        $nodeTo = $nodeGetQuote->addChild('To');
        $nodeTo->addChild('CountryCode', $rawRequest->getDestCountryId());
        $nodeTo->addChild('Postalcode', $rawRequest->getDestPostal());
        $nodeTo->addChild('City', $rawRequest->getDestCity());

        if ($this->isDutiable($rawRequest->getOrigCountryId(), $rawRequest->getDestCountryId())) {
            // IsDutiable flag and Dutiable node indicates that cargo is not a documentation
            $nodeBkgDetails->addChild('IsDutiable', 'Y');
            $nodeDutiable = $nodeGetQuote->addChild('Dutiable');
            $baseCurrencyCode = $this->_storeManager
                ->getWebsite($this->_request->getWebsiteId())
                ->getBaseCurrencyCode();
            $nodeDutiable->addChild('DeclaredCurrency', $baseCurrencyCode);
            $nodeDutiable->addChild('DeclaredValue', sprintf("%.2F", $rawRequest->getValue()));
        }

        return $xml;
    }

    /**
     * Set pick-up date in request XML object
     *
     * @param SimpleXMLElement $requestXml
     * @param string $date
     * @return SimpleXMLElement
     */
    protected function _setQuotesRequestXmlDate(SimpleXMLElement $requestXml, $date)
    {
        $requestXml->GetQuote->BkgDetails->Date = $date;

        return $requestXml;
    }

    /**
     * Parse response from DHL web service
     *
     * @param string $response
     * @return bool|DataObject|Result|Error
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _parseResponse($response)
    {
        $responseError = __('The response is in wrong format.');
        try {
            $this->xmlValidator->validate($response);
            $xml = simplexml_load_string($response);
            if (isset($xml->GetQuoteResponse->BkgDetails->QtdShp)) {
                foreach ($xml->GetQuoteResponse->BkgDetails->QtdShp as $quotedShipment) {
                    $this->_addRate($quotedShipment);
                }
            } elseif (isset($xml->AirwayBillNumber)) {
                return $this->_prepareShippingLabelContent($xml);
            } else {
                $this->_errors[] = $responseError;
            }
        } catch (DocumentValidationException $e) {
            if ($e->getCode() > 0) {
                $this->_errors[$e->getCode()] =  $e->getMessage();
            } else {
                $this->_errors[] = $e->getMessage();
            }
        }
        /* @var $result Result */
        $result = $this->_rateFactory->create();
        if ($this->_rates) {
            foreach ($this->_rates as $rate) {
                $method = $rate['service'];
                $data = $rate['data'];
                /* @var $rate Method */
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier(self::CODE);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($data['term']);
                $rate->setCost($data['price_total']);
                $rate->setPrice($data['price_total']);
                $result->append($rate);
            }
        } else {
            if (!empty($this->_errors)) {
                if ($this->_isShippingLabelFlag) {
                    throw new LocalizedException($responseError);
                }
                $this->debugErrors($this->_errors);
            }
            $result->append($this->getErrorMessage());
        }

        return $result;
    }

    /**
     * Add rate to DHL rates array
     *
     * @param SimpleXMLElement $shipmentDetails
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _addRate(SimpleXMLElement $shipmentDetails)
    {
        if (isset($shipmentDetails->ProductShortName)
            && isset($shipmentDetails->ShippingCharge)
            && isset($shipmentDetails->GlobalProductCode)
            && isset($shipmentDetails->CurrencyCode)
            && array_key_exists((string)$shipmentDetails->GlobalProductCode, $this->getAllowedMethods())
        ) {
            // DHL product code, e.g. '3', 'A', 'Q', etc.
            $dhlProduct = (string)$shipmentDetails->GlobalProductCode;
            $totalEstimate = (float)(string)$shipmentDetails->ShippingCharge;
            $currencyCode = (string)$shipmentDetails->CurrencyCode;
            $baseCurrencyCode = $this->_storeManager->getWebsite($this->_request->getWebsiteId())
                ->getBaseCurrencyCode();
            $dhlProductDescription = $this->getDhlProductTitle($dhlProduct);

            if ($currencyCode != $baseCurrencyCode) {
                /* @var $currency Currency */
                $currency = $this->_currencyFactory->create();
                $rates = $currency->getCurrencyRates($currencyCode, [$baseCurrencyCode]);
                if (!empty($rates) && isset($rates[$baseCurrencyCode])) {
                    // Convert to store display currency using store exchange rate
                    $totalEstimate = $totalEstimate * $rates[$baseCurrencyCode];
                } else {
                    $rates = $currency->getCurrencyRates($baseCurrencyCode, [$currencyCode]);
                    if (!empty($rates) && isset($rates[$currencyCode])) {
                        $totalEstimate = $totalEstimate / $rates[$currencyCode];
                    }
                    if (!isset($rates[$currencyCode]) || !$totalEstimate) {
                        $totalEstimate = false;
                        $this->_errors[] = __(
                            'We had to skip DHL method %1 because we couldn\'t find exchange rate %2 (Base Currency).',
                            $currencyCode,
                            $baseCurrencyCode
                        );
                    }
                }
            }
            if ($totalEstimate) {
                $data = [
                    'term' => $dhlProductDescription,
                    'price_total' => $this->getMethodPrice($totalEstimate, $dhlProduct),
                ];
                if (!empty($this->_rates)) {
                    foreach ($this->_rates as $product) {
                        if ($product['data']['term'] == $data['term']
                            && $product['data']['price_total'] == $data['price_total']
                        ) {
                            return $this;
                        }
                    }
                }
                $this->_rates[] = ['service' => $dhlProduct, 'data' => $data];
            } else {
                $this->_errors[] = __("Zero shipping charge for '%1'", $dhlProductDescription);
            }
        } else {
            $dhlProductDescription = false;
            if (isset($shipmentDetails->GlobalProductCode)) {
                $dhlProductDescription = $this->getDhlProductTitle((string)$shipmentDetails->GlobalProductCode);
            }
            $dhlProductDescription = $dhlProductDescription ? $dhlProductDescription : __("DHL");
            $this->_errors[] = __("Zero shipping charge for '%1'", $dhlProductDescription);
        }

        return $this;
    }

    /**
     * Returns dimension unit (cm or inch)
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getDimensionUnit()
    {
        $countryId = $this->_rawRequest->getOrigCountryId();
        $measureUnit = $this->getCountryParams($countryId)->getMeasureUnit();
        if (empty($measureUnit)) {
            throw new LocalizedException(
                __("Cannot identify measure unit for %1", $countryId)
            );
        }

        return $measureUnit;
    }

    /**
     * Returns weight unit (kg or pound)
     *
     * @return string
     * @throws LocalizedException
     */
    protected function _getWeightUnit()
    {
        $countryId = $this->_rawRequest->getOrigCountryId();
        $weightUnit = $this->getCountryParams($countryId)->getWeightUnit();
        if (empty($weightUnit)) {
            throw new LocalizedException(
                __("Cannot identify weight unit for %1", $countryId)
            );
        }

        return $weightUnit;
    }

    /**
     * Get Country Params by Country Code
     *
     * @param string $countryCode
     * @return DataObject
     *
     * @see $countryCode ISO 3166 Codes (Countries) A2
     */
    protected function getCountryParams($countryCode)
    {
        if (empty($this->_countryParams)) {
            $etcPath = $this->_configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Dhl');
            $directoryRead = $this->readFactory->create($etcPath);
            $countriesXml = $directoryRead->readFile('countries.xml');
            $this->_countryParams = $this->_xmlElFactory->create(['data' => $countriesXml]);
        }
        if (isset($this->_countryParams->{$countryCode})) {
            $countryParams = new DataObject($this->_countryParams->{$countryCode}->asArray());
        }
        return $countryParams ?? new DataObject();
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param DataObject $request
     * @return DataObject
     */
    protected function _doShipmentRequest(DataObject $request)
    {

        $this->_prepareShipmentRequest($request);
        $this->_mapRequestToShipment($request);
        $this->setRequest($request);

        return $this->_doRequest();
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param DataObject $request
     * @return $this|DataObject|boolean
     * phpcs:disable Magento2.Annotation.MethodAnnotationStructure
     * @deprecated 100.2.3
     * @see use processAdditionalValidation method instead
     */
    public function proccessAdditionalValidation(DataObject $request)
    {
        return $this->processAdditionalValidation($request);
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param DataObject $request
     * @return $this|DataObject|boolean
     */
    public function processAdditionalValidation(DataObject $request)
    {
        //Skip by item validation if there is no items in request
        if (empty($this->getAllItems($request))) {
            $this->_errors[] = __('There is no items in this order');
        }

        $countryParams = $this->getCountryParams(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            )
        );
        if (!$countryParams->getData()) {
            $this->_errors[] = __('Please specify origin country.');
        }

        if (!empty($this->_errors)) {
            $this->debugErrors($this->_errors);

            return false;
        }

        return $this;
    }

    /**
     * Return container types of carrier
     *
     * @param DataObject|null $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContainerTypes(DataObject $params = null)
    {
        return [
            self::DHL_CONTENT_TYPE_DOC => __('Documents'),
            self::DHL_CONTENT_TYPE_NON_DOC => __('Non Documents')
        ];
    }

    /**
     * Map request to shipment
     *
     * @param DataObject $request
     * @return void
     * @throws LocalizedException
     */
    protected function _mapRequestToShipment(DataObject $request)
    {
        $request->setOrigCountryId($request->getShipperAddressCountryCode());
        $this->setRawRequest($request);
        $customsValue = 0;
        $packageWeight = 0;
        $packages = $request->getPackages();
        foreach ($packages as &$piece) {
            $params = $piece['params'];
            if ($params['width'] || $params['length'] || $params['height']) {
                $minValue = $this->_getMinDimension($params['dimension_units']);
                if ($params['width'] < $minValue || $params['length'] < $minValue || $params['height'] < $minValue) {
                    $message = __('Height, width and length should be equal or greater than %1', $minValue);
                    throw new LocalizedException($message);
                }
            }

            $weightUnits = $piece['params']['weight_units'];
            $piece['params']['height'] = $this->_getDimension($piece['params']['height'], $weightUnits);
            $piece['params']['length'] = $this->_getDimension($piece['params']['length'], $weightUnits);
            $piece['params']['width'] = $this->_getDimension($piece['params']['width'], $weightUnits);
            $piece['params']['dimension_units'] = $this->_getDimensionUnit();
            $piece['params']['weight'] = $this->_getWeight($piece['params']['weight'], false, $weightUnits);
            $piece['params']['weight_units'] = $this->_getWeightUnit();

            $customsValue += $piece['params']['customs_value'];
            $packageWeight += $piece['params']['weight'];
        }

        $request->setPackages($packages)
            ->setPackageWeight($packageWeight)
            ->setPackageValue($customsValue)
            ->setValueWithDiscount($customsValue)
            ->setPackageCustomsValue($customsValue)
            ->setFreeMethodWeight(0);
    }

    /**
     * Retrieve minimum allowed value for dimensions in given dimension unit
     *
     * @param string $dimensionUnit
     * @return int
     */
    protected function _getMinDimension($dimensionUnit)
    {
        return $dimensionUnit == "CENTIMETER" ? self::DIMENSION_MIN_CM : self::DIMENSION_MIN_IN;
    }

    /**
     * Do rate request and handle errors
     *
     * @return Result|DataObject
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    protected function _doRequest()
    {
        $rawRequest = $this->_request;

        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<req:ShipmentRequest' .
            ' xmlns:req="http://www.dhl.com"' .
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
            ' xsi:schemaLocation="http://www.dhl.com ship-val-global-req.xsd"' .
            ' schemaVersion="10.0" />';
        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);

        $nodeRequest = $xml->addChild('Request', '', '');
        $nodeServiceHeader = $nodeRequest->addChild('ServiceHeader');
        $nodeServiceHeader->addChild('MessageTime', $this->buildMessageTimestamp());
        // MessageReference must be 28 to 32 chars.
        $nodeServiceHeader->addChild(
            'MessageReference',
            $this->buildMessageReference(self::SERVICE_PREFIX_SHIPVAL)
        );
        $nodeServiceHeader->addChild('SiteID', (string)$this->getConfigData('id'));
        $nodeServiceHeader->addChild('Password', (string)$this->getConfigData('password'));

        $nodeMetaData = $nodeRequest->addChild('MetaData');
        $nodeMetaData->addChild('SoftwareName', $this->buildSoftwareName());
        $nodeMetaData->addChild('SoftwareVersion', $this->buildSoftwareVersion());

        $originRegion = $this->getCountryParams(
            $this->_scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $this->getStore()
            )
        )->getRegion();
        if ($originRegion) {
            $xml->addChild('RegionCode', $originRegion, '');
        }
        $xml->addChild('RequestedPickupTime', 'N', '');
        $xml->addChild('LanguageCode', 'EN', '');

        /** Billing */
        $nodeBilling = $xml->addChild('Billing', '', '');
        $nodeBilling->addChild('ShipperAccountNumber', (string)substr($this->getConfigData('account'), 0, 9));
        /**
         * Method of Payment:
         * S (Shipper)
         * R (Receiver)
         * T (Third Party)
         */
        $nodeBilling->addChild('ShippingPaymentType', 'S');

        /**
         * Shipment bill to account – required if Shipping PaymentType is other than 'S'
         */
        $nodeBilling->addChild('BillingAccountNumber', (string)substr($this->getConfigData('account'), 0, 9));
        if ($this->isDutiable(
            $rawRequest->getShipperAddressCountryCode(),
            $rawRequest->getRecipientAddressCountryCode()
        )) {
            $nodeBilling->addChild('DutyAccountNumber', (string)substr($this->getConfigData('account'), 0, 9));
        }

        /** Receiver */
        $nodeConsignee = $xml->addChild('Consignee', '', '');

        $companyName = $rawRequest->getRecipientContactCompanyName() ? $rawRequest
            ->getRecipientContactCompanyName() : $rawRequest
            ->getRecipientContactPersonName();

        $nodeConsignee->addChild('CompanyName', is_string($companyName) ? substr($companyName, 0, 60) : '');

        $address = $rawRequest->getRecipientAddressStreet1() . ' ' . $rawRequest->getRecipientAddressStreet2();
        $address = $this->string->split($address, 45, false, true);
        if (is_array($address)) {
            $addressLineNumber = 1;
            foreach ($address as $addressLine) {
                if ($addressLineNumber > 3) {
                    break;
                }
                $nodeConsignee->addChild('AddressLine'.$addressLineNumber, $addressLine);
                $addressLineNumber++;
            }
        } else {
            $nodeConsignee->addChild('AddressLine1', $address);
        }

        $nodeConsignee->addChild('City', $rawRequest->getRecipientAddressCity());
        $recipientAddressStateOrProvinceCode = $rawRequest->getRecipientAddressStateOrProvinceCode();
        if ($recipientAddressStateOrProvinceCode) {
            $nodeConsignee->addChild('Division', $recipientAddressStateOrProvinceCode);
        }
        $nodeConsignee->addChild('PostalCode', $rawRequest->getRecipientAddressPostalCode());
        $nodeConsignee->addChild('CountryCode', $rawRequest->getRecipientAddressCountryCode());
        $nodeConsignee->addChild(
            'CountryName',
            $this->getCountryParams($rawRequest->getRecipientAddressCountryCode())->getName()
        );
        $nodeContact = $nodeConsignee->addChild('Contact');
        $recipientContactPersonName = is_string($rawRequest->getRecipientContactPersonName()) ?
            substr($rawRequest->getRecipientContactPersonName(), 0, 34) : '';
        $recipientContactPhoneNumber = is_string($rawRequest->getRecipientContactPhoneNumber()) ?
            substr($rawRequest->getRecipientContactPhoneNumber(), 0, 24) : '';
        $nodeContact->addChild('PersonName', $recipientContactPersonName);
        $nodeContact->addChild('PhoneNumber', $recipientContactPhoneNumber);

        /**
         * Commodity
         * The CommodityCode element contains commodity code for shipment contents. Its
         * value should lie in between 1 to 9999.This field is mandatory.
         */
        $nodeCommodity = $xml->addChild('Commodity', '', '');
        $nodeCommodity->addChild('CommodityCode', substr('01', 0, 18));

        /** Dutiable */
        if ($this->isDutiable(
            $rawRequest->getShipperAddressCountryCode(),
            $rawRequest->getRecipientAddressCountryCode()
        )) {
            $nodeDutiable = $xml->addChild('Dutiable', '', '');
            $nodeDutiable->addChild(
                'DeclaredValue',
                sprintf("%.2F", $rawRequest->getOrderShipment()->getOrder()->getSubtotal())
            );
            $baseCurrencyCode = $this->_storeManager->getWebsite($rawRequest->getWebsiteId())->getBaseCurrencyCode();
            $nodeDutiable->addChild('DeclaredCurrency', $baseCurrencyCode);
            $nodeDutiable->addChild('TermsOfTrade', 'DAP');

            /** Export Declaration */
            $this->addExportDeclaration($xml, $rawRequest);
        }
        /**
         * Reference
         * This element identifies the reference information. It is an optional field in the
         * shipment validation request. Only the first reference will be taken currently.
         */
        $nodeReference = $xml->addChild('Reference', '', '');
        $nodeReference->addChild('ReferenceID', 'shipment reference');
        $nodeReference->addChild('ReferenceType', 'St');

        /** Shipment Details */
        $this->_shipmentDetails($xml, $rawRequest);

        /** Shipper */
        $nodeShipper = $xml->addChild('Shipper', '', '');
        $nodeShipper->addChild('ShipperID', (string)substr($this->getConfigData('account'), 0, 9));
        $nodeShipper->addChild('CompanyName', $rawRequest->getShipperContactCompanyName());
        $nodeShipper->addChild('RegisteredAccount', (string)substr($this->getConfigData('account'), 0, 9));

        $address = $rawRequest->getShipperAddressStreet1() . ' ' . $rawRequest->getShipperAddressStreet2();
        $address = $this->string->split($address, 45, false, true);
        if (is_array($address)) {
            $addressLineNumber = 1;
            foreach ($address as $addressLine) {
                if ($addressLineNumber > 3) {
                    break;
                }
                $nodeShipper->addChild('AddressLine'.$addressLineNumber, $addressLine);
                $addressLineNumber++;
            }
        } else {
            $nodeShipper->addChild('AddressLine1', $address);
        }

        $nodeShipper->addChild('City', $rawRequest->getShipperAddressCity());
        $shipperAddressStateOrProvinceCode = $rawRequest->getShipperAddressStateOrProvinceCode();
        if ($shipperAddressStateOrProvinceCode) {
            $nodeShipper->addChild('Division', $shipperAddressStateOrProvinceCode);
        }
        $nodeShipper->addChild('PostalCode', $rawRequest->getShipperAddressPostalCode());
        $nodeShipper->addChild('CountryCode', $rawRequest->getShipperAddressCountryCode());
        $nodeShipper->addChild(
            'CountryName',
            $this->getCountryParams($rawRequest->getShipperAddressCountryCode())->getName()
        );
        $nodeContact = $nodeShipper->addChild('Contact', '', '');
        $shipperContactPersonName = is_string($rawRequest->getShipperContactPersonName()) ?
            substr($rawRequest->getShipperContactPersonName(), 0, 34) : '';
        $shipperContactPhoneNumber = is_string($rawRequest->getShipperContactPhoneNumber()) ?
            substr($rawRequest->getShipperContactPhoneNumber(), 0, 24) : '';
        $nodeContact->addChild('PersonName', $shipperContactPersonName);
        $nodeContact->addChild('PhoneNumber', $shipperContactPhoneNumber);

        $xml->addChild('LabelImageFormat', 'PDF', '');

        $request = $xml->asXML();
        if ($request && !(mb_detect_encoding($request) == 'UTF-8')) {
            $request = mb_convert_encoding($request, 'UTF-8');
        }

        $responseBody = $this->_getCachedQuotes($request);
        if ($responseBody === null) {
            $debugData = ['request' => $this->filterDebugData($request)];
            try {
                $response = $this->httpClient->request(
                    new Request(
                        $this->getGatewayURL(),
                        Request::METHOD_POST,
                        ['Content-Type' => 'application/xml'],
                        $request
                    )
                );
                $responseBody = mb_convert_encoding($response->get()->getBody(), 'ISO-8859-1', 'UTF-8');
                $debugData['result'] = $this->filterDebugData($responseBody);
                $this->_setCachedQuotes($request, $responseBody);
            } catch (Exception $e) {
                $this->_errors[$e->getCode()] = $e->getMessage();
                $responseBody = '';
            }
            $this->_debug($debugData);
        }
        $this->_isShippingLabelFlag = true;
        return $this->_parseResponse($responseBody);
    }

    /**
     * Generation Shipment Details Node according to origin region
     *
     * @param Element $xml
     * @param RateRequest $rawRequest
     * @param string $originRegion
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _shipmentDetails($xml, $rawRequest, $originRegion = '')
    {
        $nodeShipmentDetails = $xml->addChild('ShipmentDetails', '', '');

        $nodePieces = $nodeShipmentDetails->addChild('Pieces', '', '');

        /*
         * Package type
         * EE (DHL Express Envelope), OD (Other DHL Packaging), CP (Custom Packaging)
         * DC (Document), DM (Domestic), ED (Express Document), FR (Freight)
         * BD (Jumbo Document), BP (Jumbo Parcel), JD (Jumbo Junior Document)
         * JP (Jumbo Junior Parcel), PA (Parcel), DF (DHL Flyer)
         */
        $i = 0;
        foreach ($rawRequest->getPackages() as $package) {
            $nodePiece = $nodePieces->addChild('Piece', '', '');
            $packageType = 'EE';
            if ($package['params']['container'] == self::DHL_CONTENT_TYPE_NON_DOC) {
                $packageType = 'CP';
            }
            $nodePiece->addChild('PieceID', ++$i);
            $nodePiece->addChild('PackageType', $packageType);
            $nodePiece->addChild('Weight', sprintf('%.3f', $package['params']['weight']));
            $params = $package['params'];
            if ($params['width'] && $params['length'] && $params['height']) {
                $nodePiece->addChild('Width', (string) round((float) $params['width']));
                $nodePiece->addChild('Height', (string) round((float) $params['height']));
                $nodePiece->addChild('Depth', (string) round((float) $params['length']));
            }
            $content = [];
            foreach ($package['items'] as $item) {
                $content[] = $item['name'];
            }
            $nodePiece->addChild('PieceContents', $this->string->substr(implode(',', $content), 0, 34));
        }

        $nodeShipmentDetails->addChild('WeightUnit', substr($this->_getWeightUnit(), 0, 1));
        $nodeShipmentDetails->addChild('GlobalProductCode', $rawRequest->getShippingMethod());
        $nodeShipmentDetails->addChild('LocalProductCode', $rawRequest->getShippingMethod());
        $nodeShipmentDetails->addChild(
            'Date',
            $this->_coreDate->date('Y-m-d', strtotime('now + 1day'))
        );
        $nodeShipmentDetails->addChild('Contents', 'DHL Parcel');

        $nodeShipmentDetails->addChild('DimensionUnit', substr($this->_getDimensionUnit(), 0, 1));
        $contentType = isset($package['params']['container']) ? $package['params']['container'] : '';
        $packageType = $contentType === self::DHL_CONTENT_TYPE_NON_DOC ? 'CP' : 'EE';
        $nodeShipmentDetails->addChild('PackageType', $packageType);
        if ($this->isDutiable(
            $rawRequest->getShipperAddressCountryCode(),
            $rawRequest->getRecipientAddressCountryCode()
        )) {
            $nodeShipmentDetails->addChild('IsDutiable', 'Y');
        }
        $nodeShipmentDetails->addChild(
            'CurrencyCode',
            $this->_storeManager->getWebsite($this->_request->getWebsiteId())->getBaseCurrencyCode()
        );
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\Result|null
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getXMLTracking($trackings);

        return $this->_result;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $trackings
     * @return void
     */
    protected function _getXMLTracking($trackings)
    {
        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<req:KnownTrackingRequest' .
            ' xmlns:req="http://www.dhl.com"' .
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
            ' xsi:schemaLocation="http://www.dhl.com TrackingRequestKnown-1.0.xsd"' .
            ' schemaVersion="1.0" />';

        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);

        $requestNode = $xml->addChild('Request', '', '');
        $serviceHeaderNode = $requestNode->addChild('ServiceHeader', '', '');
        $serviceHeaderNode->addChild('MessageTime', $this->buildMessageTimestamp());
        $serviceHeaderNode->addChild('MessageReference', $this->buildMessageReference(self::SERVICE_PREFIX_TRACKING));
        $serviceHeaderNode->addChild('SiteID', (string)$this->getConfigData('id'));
        $serviceHeaderNode->addChild('Password', (string)$this->getConfigData('password'));

        $xml->addChild('LanguageCode', 'EN', '');
        foreach ($trackings as $tracking) {
            $xml->addChild('AWBNumber', $tracking, '');
        }
        /**
         * Checkpoint details selection flag
         * LAST_CHECK_POINT_ONLY
         * ALL_CHECK_POINTS
         */
        $xml->addChild('LevelOfDetails', 'ALL_CHECK_POINTS', '');

        /**
         * Value that indicates for getting the tracking details with the additional
         * piece details and its respective Piece Details, Piece checkpoints along with
         * Shipment Details if queried.
         *
         * S-Only Shipment Details
         * B-Both Shipment & Piece Details
         * P-Only Piece Details
         * Default is ‘S’
         */
        //$xml->addChild('PiecesEnabled', 'ALL_CHECK_POINTS');

        $request = $xml->asXML();
        $request = mb_convert_encoding($request, 'UTF-8');

        $responseBody = $this->_getCachedQuotes($request);
        if ($responseBody === null) {
            $debugData = ['request' => $this->filterDebugData($request)];
            try {
                $response = $this->httpClient->request(
                    new Request(
                        $this->getGatewayURL(),
                        Request::METHOD_POST,
                        ['Content-Type' => 'application/xml'],
                        $request
                    )
                );
                $responseBody = $response->get()->getBody();
                $debugData['result'] = $this->filterDebugData($responseBody);
                $this->_setCachedQuotes($request, $responseBody);
            } catch (Exception $e) {
                $this->_errors[$e->getCode()] = $e->getMessage();
                $responseBody = '';
            }
            $this->_debug($debugData);
        }

        $this->_parseXmlTrackingResponse($trackings, $responseBody);
    }

    /**
     * Parse xml tracking response
     *
     * @param string[] $trackings
     * @param string $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parseXmlTrackingResponse($trackings, $response)
    {
        $errorTitle = __('Unable to retrieve tracking');
        $resultArr = [];

        if (!empty(trim($response))) {
            $xml = $this->parseXml($response, Element::class);
            if (!is_object($xml)) {
                $errorTitle = __('Response is in the wrong format');
            }
            if (is_object($xml)
                && (isset($xml->Response->Status->ActionStatus)
                    && $xml->Response->Status->ActionStatus == 'Failure'
                    || isset($xml->GetQuoteResponse->Note->Condition))
            ) {
                if (isset($xml->Response->Status->Condition)) {
                    $nodeCondition = $xml->Response->Status->Condition;
                }
                $code = isset($nodeCondition->ConditionCode) ? (string)$nodeCondition->ConditionCode : 0;
                $data = isset($nodeCondition->ConditionData) ? (string)$nodeCondition->ConditionData : '';
                $this->_errors[$code] = __('Error #%1 : %2', $code, $data);
            } elseif (is_object($xml) && is_object($xml->AWBInfo)) {
                foreach ($xml->AWBInfo as $awbinfo) {
                    $awbinfoData = [];
                    $trackNum = isset($awbinfo->AWBNumber) ? (string)$awbinfo->AWBNumber : '';
                    if (!is_object($awbinfo) || !$awbinfo->ShipmentInfo) {
                        $this->_errors[$trackNum] = __('Unable to retrieve tracking');
                        continue;
                    }
                    $shipmentInfo = $awbinfo->ShipmentInfo;

                    if ($shipmentInfo->ShipmentDesc) {
                        $awbinfoData['service'] = (string)$shipmentInfo->ShipmentDesc;
                    }

                    $awbinfoData['weight'] = (string)$shipmentInfo->Weight . ' ' . (string)$shipmentInfo->WeightUnit;

                    $packageProgress = [];
                    if (isset($shipmentInfo->ShipmentEvent)) {
                        foreach ($shipmentInfo->ShipmentEvent as $shipmentEvent) {
                            $shipmentEventArray = [];
                            $shipmentEventArray['activity'] = (string)$shipmentEvent->ServiceEvent->EventCode
                                . ' ' . (string)$shipmentEvent->ServiceEvent->Description;
                            $shipmentEventArray['deliverydate'] = (string)$shipmentEvent->Date;
                            $shipmentEventArray['deliverytime'] = (string)$shipmentEvent->Time;
                            $shipmentEventArray['deliverylocation'] = (string)$shipmentEvent->ServiceArea
                                    ->Description . ' [' . (string)$shipmentEvent->ServiceArea->ServiceAreaCode . ']';
                            $packageProgress[] = $shipmentEventArray;
                        }
                        $awbinfoData['progressdetail'] = $packageProgress;
                    }
                    $resultArr[$trackNum] = $awbinfoData;
                }
            }
        }

        $result = $this->_trackFactory->create();

        if (!empty($resultArr)) {
            foreach ($resultArr as $trackNum => $data) {
                $tracking = $this->_trackStatusFactory->create();
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($trackNum);
                $tracking->addData($data);
                $result->append($tracking);
            }
        }

        if (!empty($this->_errors) || empty($resultArr)) {
            $resultArr = !empty($this->_errors) ? $this->_errors : $trackings;
            foreach ($resultArr as $trackNum => $err) {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking(!empty($this->_errors) ? $trackNum : $err);
                $error->setErrorMessage(!empty($this->_errors) ? $err : $errorTitle);
                $result->append($error);
            }
        }
        $this->_errors = [];

        $this->_result = $result;
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
     * Do request to shipment
     *
     * @param ShipmentRequest $request
     * @return array|DataObject
     * @throws LocalizedException
     */
    public function requestToShipment($request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new LocalizedException(__('No packages for request'));
        }
        $result = $this->_doShipmentRequest($request);

        $response = new DataObject(
            [
                'info' => [
                    [
                        'tracking_number' => $result->getTrackingNumber(),
                        'label_content' => $result->getShippingLabelContent(),
                    ],
                ],
            ]
        );

        $request->setMasterTrackingId($result->getTrackingNumber());

        return $response;
    }

    /**
     * Check if shipping is domestic
     *
     * @param string $origCountryCode
     * @param string $destCountryCode
     * @return bool
     */
    protected function _checkDomesticStatus($origCountryCode, $destCountryCode)
    {
        $this->_isDomestic = false;

        $origCountry = (string)$this->getCountryParams($origCountryCode)->getData('name');
        $destCountry = (string)$this->getCountryParams($destCountryCode)->getData('name');

        if (($origCountry == $destCountry)
            || (
                $this->_carrierHelper->isCountryInEU($origCountryCode)
                && $this->_carrierHelper->isCountryInEU($destCountryCode)
            )
        ) {
            $this->_isDomestic = true;
        }

        return $this->_isDomestic;
    }

    /**
     * Prepare shipping label data
     *
     * @param SimpleXMLElement $xml
     * @return DataObject
     * @throws LocalizedException
     */
    protected function _prepareShippingLabelContent(SimpleXMLElement $xml)
    {
        $result = new DataObject();
        try {
            if (!isset($xml->AirwayBillNumber) || !isset($xml->LabelImage->OutputImage)) {
                throw new LocalizedException(__('Unable to retrieve shipping label'));
            }
            $result->setTrackingNumber((string)$xml->AirwayBillNumber);
            $labelContent = (string)$xml->LabelImage->OutputImage;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result->setShippingLabelContent(base64_decode($labelContent));
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $result;
    }

    /**
     * Verify if the shipment is dutiable
     *
     * @param string $origCountryId
     * @param string $destCountryId
     *
     * @return bool
     */
    protected function isDutiable($origCountryId, $destCountryId): bool
    {
        $this->_checkDomesticStatus($origCountryId, $destCountryId);

        return !$this->_isDomestic;
    }

    /**
     * Builds a datetime string to be used as the MessageTime in accordance to the expected format.
     *
     * @param string|null $datetime
     * @return string
     */
    private function buildMessageTimestamp(string $datetime = null): string
    {
        return $this->_coreDate->date(DATE_RFC3339, $datetime);
    }

    /**
     * Builds a string to be used as the MessageReference.
     *
     * @param string $servicePrefix
     * @return string
     * @throws LocalizedException
     */
    private function buildMessageReference(string $servicePrefix): string
    {
        $validPrefixes = [
            self::SERVICE_PREFIX_QUOTE,
            self::SERVICE_PREFIX_SHIPVAL,
            self::SERVICE_PREFIX_TRACKING
        ];

        if (!in_array($servicePrefix, $validPrefixes)) {
            throw new LocalizedException(
                __("Invalid service prefix \"$servicePrefix\" provided while attempting to build MessageReference")
            );
        }

        return str_replace('.', '', uniqid("MAGE_{$servicePrefix}_", true));
    }

    /**
     * Builds a string to be used as the request SoftwareName.
     *
     * @return string
     */
    private function buildSoftwareName(): string
    {
        return substr($this->productMetadata->getName(), 0, 30);
    }

    /**
     * Builds a string to be used as the request SoftwareVersion.
     *
     * @return string
     */
    private function buildSoftwareVersion(): string
    {
        return substr($this->productMetadata->getVersion(), 0, 10);
    }

    /**
     * Get the gateway URL
     *
     * @return string
     */
    private function getGatewayURL(): string
    {
        if ($this->getConfigData('sandbox_mode')) {
            return (string)$this->getConfigData('sandbox_url');
        } else {
            return (string)$this->getConfigData('gateway_url');
        }
    }

    /**
     * Generating Export Declaration Details
     *
     * @param Element $xml
     * @param ShipmentRequest $rawRequest
     * @return void
     */
    private function addExportDeclaration(Element $xml, ShipmentRequest $rawRequest): void
    {
        $nodeExportDeclaration = $xml->addChild('ExportDeclaration', '', '');
        $nodeExportDeclaration->addChild(
            'InvoiceNumber',
            $rawRequest->getOrderShipment()->getOrder()->hasInvoices()
                ? $this->getInvoiceNumbers($rawRequest)
                : $rawRequest->getOrderShipment()->getOrder()->getIncrementId()
        );
        $nodeExportDeclaration->addChild(
            'InvoiceDate',
            date("Y-m-d", strtotime((string)$rawRequest->getOrderShipment()->getOrder()->getCreatedAt()))
        );
        $exportItems = $rawRequest->getPackages();
        foreach ($exportItems as $exportItem) {
            $itemWeightUnit = $exportItem['params']['weight_units'] ? substr(
                $exportItem['params']['weight_units'],
                0,
                1
            ) : 'L';
            foreach ($exportItem['items'] as $itemNo => $itemData) {
                $nodeExportItem = $nodeExportDeclaration->addChild('ExportLineItem', '', '');
                $nodeExportItem->addChild('LineNumber', $itemNo);
                $nodeExportItem->addChild('Quantity', $itemData['qty']);
                $nodeExportItem->addChild('QuantityUnit', 'PCS');
                $nodeExportItem->addChild('Description', $itemData['name']);
                $nodeExportItem->addChild('Value', $itemData['price']);
                $nodeItemWeight = $nodeExportItem->addChild('Weight', '', '');
                $nodeItemWeight->addChild('Weight', $itemData['weight']);
                $nodeItemWeight->addChild('WeightUnit', $itemWeightUnit);
                $nodeItemGrossWeight = $nodeExportItem->addChild('GrossWeight');
                $nodeItemGrossWeight->addChild('Weight', $itemData['weight']);
                $nodeItemGrossWeight->addChild('WeightUnit', $itemWeightUnit);
                $nodeExportItem->addChild('ManufactureCountryCode', $rawRequest->getShipperAddressCountryCode());
            }
        }
    }

    /**
     * Fetching Shipment Order Invoice No
     *
     * @param ShipmentRequest $rawRequest
     * @return string
     */
    private function getInvoiceNumbers(ShipmentRequest $rawRequest): string
    {
        $invoiceNumbers = [];
        $order = $rawRequest->getOrderShipment()->getOrder();
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceNumbers[] = $invoice->getIncrementId();
        }
        return implode(',', $invoiceNumbers);
    }
}
