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
 * DHL shipping implementation
 *
 * @category   Magento
 * @package    Magento_Usa
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Usa\Model\Shipping\Carrier;

class Dhl
    extends \Magento\Usa\Model\Shipping\Carrier\AbstractCarrier
    implements \Magento\Shipping\Model\Carrier\CarrierInterface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'dhl';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     *
     * @var \Magento\Shipping\Model\Rate\Request|null
     */
    protected $_request = null;

    /**
     * Raw rate request data
     *
     * @var \Magento\Object|null
     */
    protected $_rawRequest = null;

    /**
     * Rate result data
     *
     * @var \Magento\Shipping\Model\Rate\Result|null
     */
    protected $_result = null;

    /**
     * Errors placeholder
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Dhl rates result
     *
     * @var array
     */
    protected $_dhlRates = array();

    /**
     * Default gateway url
     *
     * @var string
     */
    protected $_defaultGatewayUrl = 'https://eCommerce.airborne.com/ApiLandingTest.asp';

    /**
     * Container types that could be customized
     *
     * @var array
     */
    protected $_customizableContainerTypes = array('P');

    const SUCCESS_CODE = 203;
    const SUCCESS_LABEL_CODE = 100;

    const ADDITIONAL_PROTECTION_ASSET = 'AP';
    const ADDITIONAL_PROTECTION_NOT_REQUIRED = 'NR';

    const ADDITIONAL_PROTECTION_VALUE_CONFIG = 0;
    const ADDITIONAL_PROTECTION_VALUE_SUBTOTAL = 1;
    const ADDITIONAL_PROTECTION_VALUE_SUBTOTAL_WITH_DISCOUNT = 2;

    const ADDITIONAL_PROTECTION_ROUNDING_FLOOR = 0;
    const ADDITIONAL_PROTECTION_ROUNDING_CEIL = 1;
    const ADDITIONAL_PROTECTION_ROUNDING_ROUND = 2;

    /**
     * Usa data
     *
     * @var \Magento\Usa\Helper\Data
     */
    protected $_usaData = null;

    /**
     * Core string
     *
     * @var \Magento\Core\Helper\String
     */
    protected $_coreString = null;

    /**
     * Dhl constructor
     *
     * @param \Magento\Core\Helper\String $coreString
     * @param \Magento\Usa\Helper\Data $usaData
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
        \Magento\Core\Helper\String $coreString,
        \Magento\Usa\Helper\Data $usaData,
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
        $this->_coreString = $coreString;
        $this->_usaData = $usaData;
        parent::__construct(
            $xmlElFactory, $rateFactory, $rateMethodFactory, $trackFactory, $trackErrorFactory, $trackStatusFactory,
            $regionFactory, $countryFactory, $currencyFactory, $directoryData, $coreStoreConfig, $rateErrorFactory,
            $logAdapterFactory, $data
        );
    }

    /**
     * Collect and get rates
     *
     * @param \Magento\Shipping\Model\Rate\Request $request
     * @return bool|\Magento\Shipping\Model\Rate\Result|null
     */
    public function collectRates(\Magento\Shipping\Model\Rate\Request $request)
    {
        if (!$this->getConfigFlag($this->_activeFlag)) {
            return false;
        }

        $requestDhl = clone $request;
        $origCompanyName = $requestDhl->getOrigCompanyName();
        if (!$origCompanyName) {
            $origCompanyName = $this->_coreStoreConfig->getConfig(
                \Magento\Core\Model\Store::XML_PATH_STORE_STORE_NAME,
                $requestDhl->getStoreId()
            );
        }

        $origCountryId = $requestDhl->getOrigCountryId();
        if (!$origCountryId) {
            $origCountryId = $this->_coreStoreConfig->getConfig(
                \Magento\Shipping\Model\Shipping::XML_PATH_STORE_COUNTRY_ID,
                $requestDhl->getStoreId()
            );
        }
        $origState = $requestDhl->getOrigState();
        if (!$origState) {
            $origState = $this->_coreStoreConfig->getConfig(
                \Magento\Shipping\Model\Shipping::XML_PATH_STORE_REGION_ID,
                $requestDhl->getStoreId()
            );
        }
        $origCity = $requestDhl->getOrigCity();
        if (!$origCity) {
            $origCity = $this->_coreStoreConfig->getConfig(
                \Magento\Shipping\Model\Shipping::XML_PATH_STORE_CITY,
                $requestDhl->getStoreId()
            );
        }

        $origPostcode = $requestDhl->getOrigPostcode();
        if (!$origPostcode) {
            $origPostcode = $this->_coreStoreConfig->getConfig(
                \Magento\Shipping\Model\Shipping::XML_PATH_STORE_ZIP,
                $requestDhl->getStoreId()
            );
        }
        $requestDhl->setOrigCompanyName($origCompanyName)
            ->setCountryId($origCountryId)
            ->setOrigState($origState)
            ->setOrigCity($origCity)
            ->setOrigPostal($origPostcode);
        $this->setRequest($requestDhl);
        $this->_result = $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * Prepare and set request in property of current instance
     *
     * @param \Magento\Object $request
     * @return \Magento\Usa\Model\Shipping\Carrier\Dhl
     */
    public function setRequest(\Magento\Object $request)
    {
        $this->_request = $request;

        $r = new \Magento\Object();

        if ($request->getAction() == 'GenerateLabel') {
            $r->setAction('GenerateLabel');
        } else {
            $r->setAction('RateEstimate');
        }
        $r->setIsGenerateLabelReturn($request->getIsGenerateLabelReturn());

        $r->setStoreId($request->getStoreId());

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        if ($request->getDhlId()) {
            $id = $request->getDhlId();
        } else {
            $id = $this->getConfigData('id');
        }
        $r->setId($id);

        if ($request->getDhlPassword()) {
            $password = $request->getDhlPassword();
        } else {
            $password = $this->getConfigData('password');
        }
        $r->setPassword($password);

        if ($request->getDhlAccount()) {
            $accountNbr = $request->getDhlAccount();
        } else {
            $accountNbr = $this->getConfigData('account');
        }
        $r->setAccountNbr($accountNbr);

        if ($request->getDhlShippingKey()) {
            $shippingKey = $request->getDhlShippingKey();
        } else {
            $shippingKey = $this->getConfigData('shipping_key');
        }
        $r->setShippingKey($shippingKey);

        if ($request->getDhlShippingIntlKey()) {
            $shippingKey = $request->getDhlShippingIntlKey();
        } else {
            $shippingKey = $this->getConfigData('shipping_intlkey');
        }
        $r->setShippingIntlKey($shippingKey);

        if ($request->getDhlShipmentType()) {
            $shipmentType = $request->getDhlShipmentType();
        } else {
            $shipmentType = $this->getConfigData('shipment_type');
        }
        $r->setShipmentType($shipmentType);

        if ($request->getDhlDutiable()) {
            $shipmentDutible = $request->getDhlDutiable();
        } else {
            $shipmentDutible = $this->getConfigData('dutiable');
        }
        $r->setDutiable($shipmentDutible);

        if ($request->getDhlDutyPaymentType()) {
            $dutypaytype = $request->getDhlDutyPaymentType();
        } else {
            $dutypaytype = $this->getConfigData('dutypaymenttype');
        }
        $r->setDutyPaymentType($dutypaytype);

        if ($request->getDhlContentDesc()) {
            $contentdesc = $request->getDhlContentDesc();
        } else {
            $contentdesc = $this->getConfigData('contentdesc');
        }
        $r->setContentDesc($contentdesc);

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_coreStoreConfig->getConfig(
                \Magento\Shipping\Model\Shipping::XML_PATH_STORE_COUNTRY_ID,
                $r->getStoreId()
            );
        }
        $r->setOrigCountry($origCountry);

        if ($request->getOrigCountryId()) {
            $origCountryId = $request->getOrigCountryId();
        } else {
            $origCountryId = $this->_coreStoreConfig->getConfig(
                \Magento\Shipping\Model\Shipping::XML_PATH_STORE_COUNTRY_ID,
                $r->getStoreId()
            );
        }
        $r->setOrigCountryId($origCountryId);

        if ($request->getAction() == 'GenerateLabel') {
            $packageParams = $request->getPackageParams();
            $shippingWeight = $request->getPackageWeight();
            if ($packageParams->getWeightUnits() != \Zend_Measure_Weight::POUND) {
                $shippingWeight = round($this->_usaData->convertMeasureWeight(
                    $request->getPackageWeight(),
                    $packageParams->getWeightUnits(),
                    \Zend_Measure_Weight::POUND
                ));
            }
            if ($packageParams->getDimensionUnits() != \Zend_Measure_Length::INCH) {
                $packageParams->setLength(round($this->_usaData->convertMeasureDimension(
                    $packageParams->getLength(),
                    $packageParams->getDimensionUnits(),
                    \Zend_Measure_Length::INCH
                )));
                $packageParams->setWidth(round($this->_usaData->convertMeasureDimension(
                    $packageParams->getWidth(),
                    $packageParams->getDimensionUnits(),
                    \Zend_Measure_Length::INCH
                )));
                $packageParams->setHeight(round($this->_usaData->convertMeasureDimension(
                    $packageParams->getHeight(),
                    $packageParams->getDimensionUnits(),
                    \Zend_Measure_Length::INCH
                )));
            }
            $r->setPackageParams($packageParams);
        } else {
            /*
            * DHL only accepts weight as a whole number. Maximum length is 3 digits.
            */
            $shippingWeight = $request->getPackageWeight();
            if ($shipmentType != 'L') {
                $weight = $this->getTotalNumOfBoxes($shippingWeight);
                $shippingWeight = round(max(1, $weight), 0);
            }
        }

        $r->setValue(round($request->getPackageValue(), 2));
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());
        $r->setCustomsValue($request->getPackageCustomsValue());
        $r->setDestStreet(
            $this->_coreString->substr(str_replace("\n", '', $request->getDestStreet()), 0, 35)
        );
        $r->setDestStreetLine2($request->getDestStreetLine2());
        $r->setDestCity($request->getDestCity());
        $r->setOrigCompanyName($request->getOrigCompanyName());
        $r->setOrigCity($request->getOrigCity());
        $r->setOrigPhoneNumber($request->getOrigPhoneNumber());
        $r->setOrigPersonName($request->getOrigPersonName());
        $r->setOrigEmail($this->_coreStoreConfig->getConfig('trans_email/ident_general/email', $r->getStoreId()));
        $r->setOrigCity($request->getOrigCity());
        $r->setOrigPostal($request->getOrigPostal());
        $originStreet1 = $this->_coreStoreConfig->getConfig(\Magento\Shipping\Model\Shipping::XML_PATH_STORE_ADDRESS1,$r->getStoreId());
        $originStreet2 = $this->_coreStoreConfig->getConfig(\Magento\Shipping\Model\Shipping::XML_PATH_STORE_ADDRESS2, $r->getStoreId());
        $r->setOrigStreet($request->getOrigStreet() ? $request->getOrigStreet() : $originStreet2);
        $r->setOrigStreetLine2($request->getOrigStreetLine2());
        $r->setDestPhoneNumber($request->getDestPhoneNumber());
        $r->setDestPersonName($request->getDestPersonName());
        $r->setDestCompanyName($request->getDestCompanyName());


        if (is_numeric($request->getOrigState())) {
            $r->setOrigState($this->_regionFactory->create()->load($request->getOrigState())->getCode());
        } else {
            $r->setOrigState($request->getOrigState());
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        //for DHL, puero rico state for US will assume as puerto rico country
        //for puerto rico, dhl will ship as international
        if ($destCountry == self::USA_COUNTRY_ID && ($request->getDestPostcode() == '00912'
                                                     || $request->getDestRegionCode() == self::PUERTORICO_COUNTRY_ID)
        ) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        $r->setDestCountryId($destCountry);
        $r->setDestState($request->getDestRegionCode());

        $r->setWeight($shippingWeight);
        $r->setFreeMethodWeight($request->getFreeMethodWeight());

        $r->setOrderShipment($request->getOrderShipment());

        if ($request->getPackageId()) {
            $r->setPackageId($request->getPackageId());
        }

        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->_rawRequest = $r;
        return $this;
    }

    /**
     * Get result of request
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Get quotes
     *
     * @return \Magento\Shipping\Model\Rate\Result
     */
    protected function _getQuotes()
    {
        return $this->_getXmlQuotes();
    }

    /**
     * Set free method request
     *
     * @param  $freeMethod
     * @return void
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        $r = $this->_rawRequest;

        $r->setFreeMethodRequest(true);
        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $freeWeight = round(max(1, $weight), 0);
        $r->setWeight($freeWeight);
        $r->setService($freeMethod);
    }

    /**
     * Get shipping date
     *
     * @param bool $domestic
     * @return string
     */
    protected function _getShipDate($domestic = true)
    {
        if ($domestic) {
            $days = explode(',', $this->getConfigData('shipment_days'));
        } else {
            $days = explode(',', $this->getConfigData('intl_shipment_days'));
        }

        if (!$days) {
            return date('Y-m-d');
        }

        $i = 0;
        $weekday = date('w');
        while (!in_array($weekday, $days) && $i < 10) {
            $i++;
            $weekday = date('w', strtotime("+$i day"));
        }

        return date('Y-m-d', strtotime("+$i day"));
    }

    /**
     * Get xml quotes
     *
     * @return \Magento\Core\Model\AbstractModel|\Magento\Object
     */
    protected function _getXmlQuotes()
    {
        return $this->_doRequest();
    }

    /**
     * Do rate request and handle errors
     *
     * @return \Magento\Shipping\Model\Rate\Result|\Magento\Object
     */
    protected function _doRequest()
    {
        $r = $this->_rawRequest;

        $xml = $this->_xmlElFactory->create(array('data' => '<?xml version = "1.0" encoding = "UTF-8"?><eCommerce/>'));
        $xml->addAttribute('action', 'Request');
        $xml->addAttribute('version', '1.1');

        $requestor = $xml->addChild('Requestor');
        $requestor->addChild('ID', $r->getId());
        $requestor->addChild('Password', $r->getPassword());

        $methods = explode(',', $this->getConfigData('allowed_methods'));
        $internationcode = $this->getCode('international_searvice');
        $hasShipCode = false;

        $shipDate = $this->_getShipDate();

        if ($r->hasService() && $r->getFreeMethodRequest()) {
            if ($r->getDestCountryId() == self::USA_COUNTRY_ID) {
                $shipment = $xml->addChild('Shipment');
                $shipKey = $r->getShippingKey();
                $r->setShipDate($shipDate);
            } else {
                $shipment = $xml->addChild('IntlShipment');
                $shipKey = $r->getShippingIntlKey();
                $r->setShipDate($this->_getShipDate(false));
                /*
                * For internation shippingment customsvalue must be posted
                */
                $shippingDuty = $shipment->addChild('Dutiable');
                $shippingDuty->addChild('DutiableFlag', ($r->getDutiable() ? 'Y' : 'N'));
                $shippingDuty->addChild('CustomsValue', $r->getValue());
                $shippingDuty->addChild('IsSEDReqd', 'N');
            }
            $hasShipCode = true;
            $this->_createShipmentXml($shipment, $shipKey);
        } else {
            if ($r->getAction() == 'GenerateLabel') {
                $methods = array($r->getService());
            }

            foreach ($methods as $method) {
                $shipment = false;
                if (in_array($method, array_keys($this->getCode('special_express')))) {
                    $r->setService('E');
                    $r->setExtendedService($this->getCode('special_express', $method));
                } else {
                    $r->setService($method);
                    $r->setExtendedService(null);
                }
                if ($r->getDestCountryId() == self::USA_COUNTRY_ID && $method != $internationcode) {
                    $shipment = $xml->addChild('Shipment');
                    $shipKey = $r->getShippingKey();
                    $r->setShipDate($shipDate);
                } elseif ($r->getDestCountryId() != self::USA_COUNTRY_ID && $method == $internationcode) {
                    $shipment = $xml->addChild('IntlShipment');
                    $shipKey = $r->getShippingIntlKey();
                    if ($r->getCustomsValue() != null && $r->getCustomsValue() != '') {
                        $customsValue =  $r->getCustomsValue();
                    } else {
                        $customsValue =  $r->getValue();
                    }

                    $r->setShipDate($this->_getShipDate(false));

                    /*
                    * For internation shippingment customsvalue must be posted
                    */
                    $shippingDuty = $shipment->addChild('Dutiable');
                    $shippingDuty->addChild('DutiableFlag', ($r->getDutiable() ? 'Y' : 'N'));
                    $shippingDuty->addChild('CustomsValue', $customsValue);
                    $shippingDuty->addChild('IsSEDReqd', 'N');
                }
                if ($shipment !== false) {
                    $hasShipCode = true;
                    $this->_createShipmentXml($shipment, $shipKey);
                }
            }
        }

        if (!$hasShipCode) {
            $this->_errors[] = __('We don\'t have a way to ship to the selected shipping address. Please choose another address or edit the current address.');
            return;
        }

        $request = $xml->asXML();
        $request = utf8_encode($request);
        $responseBody = $this->_getCachedQuotes($request);
        if ($responseBody === null) {
            $debugData = array('request' => $request);
            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultGatewayUrl;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                $responseBody = curl_exec($ch);
                curl_close($ch);

                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($request, $responseBody);
            }
            catch (\Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $responseBody = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseXmlResponse($responseBody);
    }

    /**
     * Create shipment xml
     *
     * @param  $shipment
     * @param  $shipKey
     * @return void
     */
    protected function _createShipmentXml($shipment, $shipKey)
    {
        $r = $this->_rawRequest;

        $_haz = $this->getConfigFlag('hazardous_materials');

        $_subtotal = $r->getValue();
        $_subtotalWithDiscount = $r->getValueWithDiscount();

        $_width = max(0, (double)$this->getConfigData('default_width'));
        $_height = max(0, (double)$this->getConfigData('default_height'));
        $_length = max(0, (double)$this->getConfigData('default_length'));

        $packageParams = $r->getPackageParams();
        if ($packageParams) {
            $_length = $packageParams->getLength();
            $_width = $packageParams->getWidth();
            $_height = $packageParams->getHeight();
        }

        $_apEnabled = $this->getConfigFlag('additional_protection_enabled');
        $_apUseSubtotal = $this->getConfigData('additional_protection_use_subtotal');
        $_apConfigValue = max(0, (double)$this->getConfigData('additional_protection_value'));
        $_apMinValue = max(0, (double)$this->getConfigData('additional_protection_min_value'));
        $_apValueRounding = $this->getConfigData('additional_protection_rounding');

        $apValue = 0;
        $apCode = self::ADDITIONAL_PROTECTION_NOT_REQUIRED;
        if ($_apEnabled) {
            if ($_apMinValue <= $_subtotal) {
                switch ($_apUseSubtotal) {
                    case self::ADDITIONAL_PROTECTION_VALUE_SUBTOTAL:
                        $apValue = $_subtotal;
                        break;
                    case self::ADDITIONAL_PROTECTION_VALUE_SUBTOTAL_WITH_DISCOUNT:
                        $apValue = $_subtotalWithDiscount;
                        break;
                    default:
                    case self::ADDITIONAL_PROTECTION_VALUE_CONFIG:
                        $apValue = $_apConfigValue;
                        break;
                }

                if ($apValue) {
                    $apCode = self::ADDITIONAL_PROTECTION_ASSET;


                    switch ($_apValueRounding) {
                        case self::ADDITIONAL_PROTECTION_ROUNDING_CEIL:
                            $apValue = ceil($apValue);
                            break;
                        case self::ADDITIONAL_PROTECTION_ROUNDING_ROUND:
                            $apValue = round($apValue);
                            break;
                        default:
                        case self::ADDITIONAL_PROTECTION_ROUNDING_FLOOR:
                            $apValue = floor($apValue);
                            break;
                    }
                }
            }
        }

        if ($r->getAction() == 'GenerateLabel') {
            $shipment->addAttribute('action', 'GenerateLabel');
        } else {
            $shipment->addAttribute('action', 'RateEstimate');
        }
        $shipment->addAttribute('version', '1.0');

        $shippingCredentials = $shipment->addChild('ShippingCredentials');
        $shippingCredentials->addChild('ShippingKey', $shipKey);
        $shippingCredentials->addChild('AccountNbr', $r->getAccountNbr());

        $shipmentDetail = $shipment->addChild('ShipmentDetail');
        if ($r->getAction() == 'GenerateLabel') {
            if ($this->_request->getReferenceData()) {
                $referenceData = $this->_request->getReferenceData() . $this->_request->getPackageId();
            } else {
                $referenceData = 'Order #'
                                 . $r->getOrderShipment()->getOrder()->getIncrementId()
                                 . ' P'
                                 . $r->getPackageId();
            }

            $shipmentDetail->addChild('ShipperReference', $referenceData);
        }
        $shipmentDetail->addChild('ShipDate', $r->getShipDate());
        $shipmentDetail->addChild('Service')->addChild('Code', $r->getService());
        $shipmentDetail->addChild('ShipmentType')->addChild('Code', $r->getShipmentType());
        $shipmentDetail->addChild('Weight', $r->getWeight());
        $shipmentDetail->addChild('ContentDesc', $r->getContentDesc());
        $additionalProtection = $shipmentDetail->addChild('AdditionalProtection');
        $additionalProtection->addChild('Code', $apCode);
        $additionalProtection->addChild('Value', floor($apValue));

        if ($_width || $_height || $_length) {
            $dimensions = $shipmentDetail->addChild('Dimensions');
            $dimensions->addChild('Length', $_length);
            $dimensions->addChild('Width', $_width);
            $dimensions->addChild('Height', $_height);
        }

        if ($_haz || ($r->getExtendedService())) {
            $specialServices = $shipmentDetail->addChild('SpecialServices');
        }

        if ($_haz) {
            $hazardousMaterials = $specialServices->addChild('SpecialService');
            $hazardousMaterials->addChild('Code', 'HAZ');
        }

        if ($r->getExtendedService()) {
            $extendedService = $specialServices->addChild('SpecialService');
            $extendedService->addChild('Code', $r->getExtendedService());
        }


        /*
        * R = Receiver (if receiver, need AccountNbr)
        * S = Sender
        * 3 = Third Party (if third party, need AccountNbr)
        */
        $billing = $shipment->addChild('Billing');
        $billing->addChild('Party')->addChild('Code', $r->getIsGenerateLabelReturn() ? 'R' : 'S');
        $billing->addChild('DutyPaymentType', $r->getDutyPaymentType());
        if ($r->getIsGenerateLabelReturn()) {
            $billing->addChild('AccountNbr', $r->getAccountNbr());
        }

        $sender = $shipment->addChild('Sender');
        $sender->addChild('SentBy', ($r->getOrigPersonName()));
        $sender->addChild('PhoneNbr', $r->getOrigPhoneNumber());
        $sender->addChild('Email', $r->getOrigEmail());

        $senderAddress = $sender->addChild('Address');
        $senderAddress->addChild('Street', htmlspecialchars($r->getOrigStreet() ? $r->getOrigStreet() : 'N/A'));
        $senderAddress->addChild('City', htmlspecialchars($r->getOrigCity()));
        $senderAddress->addChild('State', htmlspecialchars($r->getOrigState()));
        $senderAddress->addChild('CompanyName', htmlspecialchars($r->getOrigCompanyName()));
        /*
        * DHL xml service is using UK for united kingdom instead of GB which is a standard ISO country code
        */
        $senderAddress->addChild('Country', ($r->getOrigCountryId() == 'GB' ? 'UK' : $r->getOrigCountryId()));
        $senderAddress->addChild('PostalCode', $r->getOrigPostal());

        $receiver = $shipment->addChild('Receiver');
        $receiver->addChild('AttnTo', $r->getDestPersonName());
        $receiver->addChild('PhoneNbr', $r->getDestPhoneNumber());

        $receiverAddress = $receiver->addChild('Address');
        $receiverAddress->addChild('Street', htmlspecialchars($r->getDestStreet() ? $r->getDestStreet() : 'N/A'));
        $receiverAddress->addChild('StreetLine2',
                                   htmlspecialchars($r->getDestStreetLine2() ? $r->getDestStreetLine2() : 'N/A')
        );
        $receiverAddress->addChild('City', htmlspecialchars($r->getDestCity()));
        $receiverAddress->addChild('State', htmlspecialchars($r->getDestState()));
        $receiverAddress->addChild('CompanyName',
                                   htmlspecialchars($r->getDestCompanyName() ? $r->getDestCompanyName() : 'N/A')
        );

        /*
        * DHL xml service is using UK for united kingdom instead of GB which is a standard ISO country code
        */
        $receiverAddress->addChild('Country', ($r->getDestCountryId() == 'GB' ? 'UK' : $r->getDestCountryId()));
        $receiverAddress->addChild('PostalCode', $r->getDestPostal());

        if ($r->getAction() == 'GenerateLabel') {
            $label = $shipment->addChild('ShipmentProcessingInstructions')->addChild('Label');
            $label->addChild('ImageType', 'PNG');
        }
    }

    /**
     * Parse xml response and return result
     *
     * @param string $response
     * @return \Magento\Shipping\Model\Rate\Result|\Magento\Object
     */
    protected function _parseXmlResponse($response)
    {
        $r = $this->_rawRequest;
        $costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve quotes';

        $tr = get_html_translation_table(HTML_ENTITIES);
        unset($tr['<'], $tr['>'], $tr['"']);
        $response = str_replace(array_keys($tr), array_values($tr), $response);

        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                $xml = simplexml_load_string($response);
                if (is_object($xml)) {
                    if (
                        is_object($xml->Faults)
                        && is_object($xml->Faults->Fault)
                        && is_object($xml->Faults->Fault->Code)
                        && is_object($xml->Faults->Fault->Description)
                        && is_object($xml->Faults->Fault->Context)
                    ) {
                        $code = (string)$xml->Faults->Fault->Code;
                        $description = $xml->Faults->Fault->Description;
                        $context = $xml->Faults->Fault->Context;
                        $this->_errors[$code] = __('Error #%1 : %2 (%3)', $code, $description, $context);
                    } else {
                        if ($r->getDestCountryId() == self::USA_COUNTRY_ID) {
                            if ($xml->Shipment) {
                                foreach ($xml->Shipment as $shipXml) {
                                    $this->_parseXmlObject($shipXml);
                                }
                            } else {
                                $this->_errors[] = __('Shipment is not available.');
                            }
                        } else {
                            $shipXml = $xml->IntlShipment;
                            $this->_parseXmlObject($shipXml);
                        }
                        $shipXml = (($r->getDestCountryId() == self::USA_COUNTRY_ID)
                            ? $xml->Shipment
                            : $xml->IntlShipment
                        );
                    }
                }
            } else {
                $this->_errors[] = __('Please format your response correctly.');
            }
        }

        if ($this->_rawRequest->getAction() == 'GenerateLabel') {
            $result = new \Magento\Object();
            if (!empty($this->_errors)) {
                $result->setErrors(implode($this->_errors, '; '));
            } else {
                if ($xml !== false) {
                    if ($r->getDestCountryId() == self::USA_COUNTRY_ID) {
                        $shippingLabelContent = base64_decode((string)$xml->Shipment->Label->Image);
                        $trackingNumber = (string)$xml->Shipment->ShipmentDetail->AirbillNbr;
                    } else {
                        $shippingLabelContent = base64_decode((string)$xml->IntlShipment->Label->Image);
                        $trackingNumber = (string)$xml->IntlShipment->ShipmentDetail->AirbillNbr;
                    }
                }
                $result->setShippingLabelContent($shippingLabelContent);
                $result->setTrackingNumber($trackingNumber);
            }
            return $result;
        } else {
            $result = $this->_rateFactory->create();
            if ($this->_dhlRates) {
                foreach ($this->_dhlRates as $rate) {
                    $method = $rate['service'];
                    $data = $rate['data'];
                    $rate = $this->_rateMethodFactory->create();
                    $rate->setCarrier('dhl');
                    $rate->setCarrierTitle($this->getConfigData('title'));
                    $rate->setMethod($method);
                    $rate->setMethodTitle($data['term']);
                    $rate->setCost($data['price_total']);
                    $rate->setPrice($data['price_total']);
                    $result->append($rate);
                }
            } else if (!empty($this->_errors)) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier('dhl');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
                $result->append($error);
            }
            return $result;
        }
    }

    /**
     * Parse xml object
     *
     * @param mixed $shipXml
     * @return \Magento\Usa\Model\Shipping\Carrier\Dhl
     */
    protected function _parseXmlObject($shipXml)
    {
        if (
            is_object($shipXml->Faults)
            && is_object($shipXml->Faults->Fault)
            && is_object($shipXml->Faults->Fault->Desc)
            && intval($shipXml->Faults->Fault->Code) != self::SUCCESS_CODE
            && intval($shipXml->Faults->Fault->Code) != self::SUCCESS_LABEL_CODE
        ) {
            $code = (string)$shipXml->Faults->Fault->Code;
            $description = $shipXml->Faults->Fault->Desc;
            $this->_errors[$code] = __('Error #%1: %2', $code, $description);
        } elseif (
            is_object($shipXml->Faults)
            && is_object($shipXml->Result->Code)
            && is_object($shipXml->Result->Desc)
            && intval($shipXml->Result->Code) != self::SUCCESS_CODE
            && intval($shipXml->Result->Code) != self::SUCCESS_LABEL_CODE
        ) {
            $code = (string)$shipXml->Result->Code;
            $description = $shipXml->Result->Desc;
            $this->_errors[$code] = __('Error #%1: %2', $code, $description);
        } else {
            $this->_addRate($shipXml);
        }
        return $this;
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
        static $codes;
        $codes = array(
            'service' => array(
                'IE' => __('International Express'),
                'E SAT' => __('Express Saturday'),
                'E 10:30AM' => __('Express 10:30 AM'),
                'E' => __('Express'),
                'N' => __('Next Afternoon'),
                'S' => __('Second Day Service'),
                'G' => __('Ground'),
            ),
            'shipment_type' => array(
                'L' => __('Letter'),
                'P' => __('Package'),
            ),
            'international_searvice' => 'IE',
            'dutypayment_type' => array(
                'S' => __('Sender'),
                'R' => __('Receiver'),
                '3' => __('Third Party'),
            ),

            'special_express' => array(
                'E SAT' => 'SAT',
                'E 10:30AM' => '1030',
            ),

            'descr_to_service' => array(
                'E SAT' => 'Saturday',
                'E 10:30AM' => '10:30 A.M',
            ),

        );


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
     * Parse xml and add rates to instance property
     *
     * @param mixed $shipXml
     * @return void
     */
    protected function _addRate($shipXml)
    {
        $r = $this->_rawRequest;
        $services = $this->getCode('service');
        $regexps = $this->getCode('descr_to_service');
        $desc = ($shipXml->EstimateDetail) ? (string)$shipXml->EstimateDetail->ServiceLevelCommitment->Desc : null;

        $totalEstimate = $shipXml->EstimateDetail
                ? (string)$shipXml->EstimateDetail->RateEstimate->TotalChargeEstimate
                : null;
        /*
        * DHL can return with empty result and success code
        * we need to make sure there is shipping estimate and code
        */
        if ($desc && $totalEstimate) {
            $service = (string)$shipXml->EstimateDetail->Service->Code;
            $description = (string)$shipXml->EstimateDetail->ServiceLevelCommitment->Desc;
            if ($service == 'E') {
                foreach ($regexps as $expService => $exp) {
                    if (preg_match('/' . preg_quote($exp, '/') . '/', $description)) {
                        $service = $expService;
                    }
                }
            }

            $data['term'] = (isset($services[$service]) ? $services[$service] : $desc);
            $data['price_total'] = $this->getMethodPrice($totalEstimate, $service);
            $this->_dhlRates[] = array('service' => $service, 'data' => $data);
        }
    }

    /**
     * Get tracking
     *
     * @param mixed $trackings
     * @return mixed
     */
    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }
        $this->_getXMLTracking($trackings);

        return $this->_result;
    }

    /**
     * Set tracking request
     *
     * @return null
     */
    protected function setTrackingReqeust()
    {
        $r = new \Magento\Object();

        $id = $this->getConfigData('id');
        $r->setId($id);

        $password = $this->getConfigData('password');
        $r->setPassword($password);

        $this->_rawTrackRequest = $r;
    }

    /**
     * Send request for tracking
     *
     * @param array $tracking
     * @return null
     */
    protected function _getXMLTracking($trackings)
    {
        $r = $this->_rawTrackRequest;

        $xml = $this->_xmlElFactory->create(array('data' => '<?xml version = "1.0" encoding = "UTF-8"?><eCommerce/>'));
        $xml->addAttribute('action', 'Request');
        $xml->addAttribute('version', '1.1');

        $requestor = $xml->addChild('Requestor');
        $requestor->addChild('ID', $r->getId());
        $requestor->addChild('Password', $r->getPassword());

        $track = $xml->addChild('Track');
        $track->addAttribute('action', 'Get');
        $track->addAttribute('version', '1.0');

        foreach ($trackings as $tracking) {
            $track->addChild('Shipment')->addChild('TrackingNbr', $tracking);
        }
        $request = $xml->asXML();
        $debugData = array('request' => $request);
        /*
         * tracking api cannot process from 3pm to 5pm PST time on Sunday
         * DHL Airborne conduts a maintainance during that period.
         */
        try {
            $url = $this->getConfigData('gateway_url');
            if (!$url) {
                $url = $this->_defaultGatewayUrl;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $responseBody = curl_exec($ch);
            $debugData['result'] = $responseBody;
            curl_close($ch);
        } catch (\Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $responseBody = '';
        }
        $this->_debug($debugData);
        $this->_parseXmlTrackingResponse($trackings, $responseBody);
    }

    /**
     * Parse xml tracking response
     *
     * @param array $trackingvalue
     * @param string $response
     * @return null
     */
    protected function _parseXmlTrackingResponse($trackings, $response)
    {
        $errorTitle = __('Unable to retrieve tracking');
        $resultArr = array();
        $errorArr = array();
        $trackingserror = array();
        $tracknum = '';
        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                $xml = simplexml_load_string($response);
                if (is_object($xml)) {
                    $trackxml = $xml->Track;
                    if (
                        is_object($xml->Faults)
                        && is_object($xml->Faults->Fault)
                        && is_object($xml->Faults->Fault->Code)
                        && is_object($xml->Faults->Fault->Description)
                        && is_object($xml->Faults->Fault->Context)
                    ) {
                        $code = (string)$xml->Faults->Fault->Code;
                        $description = $xml->Faults->Fault->Description;
                        $context = $xml->Faults->Fault->Context;
                        $errorTitle = __('Error #%1 : %2 (%3)', $code, $description, $context);
                    } elseif (is_object($trackxml) && is_object($trackxml->Shipment)) {
                        foreach ($trackxml->Shipment as $txml) {
                            $rArr = array();

                            if (is_object($txml)) {
                                $tracknum = (string)$txml->TrackingNbr;
                                if ($txml->Fault) {
                                    $code = (string)$txml->Fault->Code;
                                    $description = $txml->Fault->Description;
                                    $errorArr[$tracknum] = __('Error #%1: %2', $code, $description);
                                } elseif ($txml->Result) {
                                    $code = (int)$txml->Result->Code;
                                    if ($code === 0) {
                                        /*
                                        * Code 0== airbill  found
                                        */
                                        $rArr['service'] = (string)$txml->Service->Desc;
                                        if (isset($txml->Weight))
                                            $rArr['weight'] = (string)$txml->Weight . " lbs";
                                        if (isset($txml->Delivery)) {
                                            $rArr['deliverydate'] = (string)$txml->Delivery->Date;
                                            $rArr['deliverytime'] = (string)$txml->Delivery->Time . ':00';
                                            $rArr['status'] = __('Delivered');
                                            if (isset($txml->Delivery->Location->Desc)) {
                                                $rArr['deliverylocation'] = (string)$txml->Delivery->Location->Desc;
                                            }
                                        } elseif (isset($txml->Pickup)) {
                                            $rArr['deliverydate'] = (string)$txml->Pickup->Date;
                                            $rArr['deliverytime'] = (string)$txml->Pickup->Time . ':00';
                                            $rArr['status'] = __('Shipment picked up');
                                        } else {
                                            $rArr['status'] = (string)$txml->ShipmentType->Desc
                                                  . __(' was not delivered nor scanned');
                                        }

                                        $packageProgress = array();
                                        if (isset($txml->TrackingHistory) && isset($txml->TrackingHistory->Status)) {

                                            foreach ($txml->TrackingHistory->Status as $thistory) {
                                                $tempArr = array();
                                                $tempArr['activity'] = (string)$thistory->StatusDesc;
                                                $tempArr['deliverydate'] = (string)$thistory->Date; //YYYY-MM-DD
                                                $tempArr['deliverytime'] = (string)$thistory->Time . ':00'; //HH:MM:ss
                                                $addArr = array();
                                                if (isset($thistory->Location->City)) {
                                                    $addArr[] = (string)$thistory->Location->City;
                                                }
                                                if (isset($thistory->Location->State)) {
                                                    $addArr[] = (string)$thistory->Location->State;
                                                }
                                                if (isset($thistory->Location->CountryCode)) {
                                                    $addArr[] = (string)$thistory->Location->Country;
                                                }
                                                if ($addArr) {
                                                    $tempArr['deliverylocation'] = implode(', ', $addArr);
                                                } elseif (isset($thistory['final_delivery'])
                                                          && (string)$thistory['final_delivery'] === 'true'
                                                ) {
                                                    /*
                                                    * if the history is final delivery, there is no informationabout
                                                    * city, state and country
                                                    */
                                                    $addArr = array();
                                                    if (isset($txml->Receiver->City)) {
                                                        $addArr[] = (string)$txml->Receiver->City;
                                                    }
                                                    if (isset($thistory->Receiver->State)) {
                                                        $addArr[] = (string)$txml->Receiver->State;
                                                    }
                                                    if (isset($thistory->Receiver->CountryCode)) {
                                                        $addArr[] = (string)$txml->Receiver->Country;
                                                    }
                                                    $tempArr['deliverylocation'] = implode(', ', $addArr);
                                                }
                                                $packageProgress[] = $tempArr;
                                            }
                                            $rArr['progressdetail'] = $packageProgress;

                                        }
                                        $resultArr[$tracknum] = $rArr;
                                    } else {
                                        $description = (string)$txml->Result->Desc;
                                        if ($description)
                                            $errorArr[$tracknum] = __('Error #%1: %2', $code, $description);
                                        else
                                            $errorArr[$tracknum] = __('Unable to retrieve tracking');
                                    }
                                } else {
                                    $errorArr[$tracknum] = __('Unable to retrieve tracking');
                                }

                            }
                        }

                    }
                }
            } else {
                $errorTitle = __('Response is in the wrong format');
            }
        }

        $result = $this->_trackFactory->create();
        if ($errorArr || $resultArr) {
            foreach ($errorArr as $t => $r) {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier('dhl');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($t);
                $error->setErrorMessage($r);
                $result->append($error);
            }

            foreach ($resultArr as $t => $data) {
                $tracking = $this->_trackStatusFactory->create();
                $tracking->setCarrier('dhl');
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($t);
                $tracking->addData($data);

                $result->append($tracking);
            }
        } else {
            foreach ($trackings as $t) {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier('dhl');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($t);
                $error->setErrorMessage($errorTitle);
                $result->append($error);

            }
        }
        $this->_result = $result;
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
                        if (isset($data['status'])) {
                            $statuses .= __($data['status']) . "\n<br/>";
                        } else {
                            $statuses .= __($data['error_message']) . "\n<br/>";
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
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('service', $k);
        }
        return $arr;
    }

    /**
     * Is state province required
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        return true;
    }

    /**
     * Get additional protection value types
     *
     * @return array
     */
    public function getAdditionalProtectionValueTypes()
    {
        return array(
            self::ADDITIONAL_PROTECTION_VALUE_CONFIG => __('Configuration'),
            self::ADDITIONAL_PROTECTION_VALUE_SUBTOTAL => __('Subtotal'),
            self::ADDITIONAL_PROTECTION_VALUE_SUBTOTAL_WITH_DISCOUNT => __('Subtotal With Discount'),
        );
    }

    /**
     * Get additional protection rounding types
     *
     * @return array
     */
    public function getAdditionalProtectionRoundingTypes()
    {
        return array(
            self::ADDITIONAL_PROTECTION_ROUNDING_FLOOR => __('To Lower'),
            self::ADDITIONAL_PROTECTION_ROUNDING_CEIL => __('To Upper'),
            self::ADDITIONAL_PROTECTION_ROUNDING_ROUND => __('Round'),
        );
    }

    /**
     * Map request to shipment
     *
     * @param \Magento\Object $request
     * @return null
     */
    protected function _mapRequestToShipment(\Magento\Object $request)
    {
        $customsValue = $request->getPackageParams()->getCustomsValue();
        $request->setOrigPersonName($request->getShipperContactPersonName());
        $request->setOrigPostal($request->getShipperAddressPostalCode());
        $request->setOrigPhoneNumber($request->getShipperContactPhoneNumber());
        $request->setOrigCompanyName($request->getShipperContactCompanyName());
        $request->setOrigCountryId($request->getShipperAddressCountryCode());
        $request->setOrigState($request->getShipperAddressStateOrProvinceCode());
        $request->setOrigCity($request->getShipperAddressCity());
        $request->setOrigStreet($request->getShipperAddressStreet1() . ' ' . $request->getShipperAddressStreet2());
        $request->setOrigStreetLine2($request->getShipperAddressStreet2());

        $request->setDestPersonName($request->getRecipientContactPersonName());
        $request->setDestPostcode($request->getRecipientAddressPostalCode());
        $request->setDestPhoneNumber($request->getRecipientContactPhoneNumber());
        $request->setDestCompanyName($request->getRecipientContactCompanyName());
        $request->setDestCountryId($request->getRecipientAddressCountryCode());
        $request->setDestRegionCode($request->getRecipientAddressStateOrProvinceCode());
        $request->setDestCity($request->getRecipientAddressCity());
        $request->setDestStreet($request->getRecipientAddressStreet1());
        $request->setDestStreetLine2($request->getRecipientAddressStreet2());

        $request->setLimitMethod($request->getShippingMethod());
        $request->setPackageValue($customsValue);
        $request->setValueWithDiscount($customsValue);
        $request->setPackageCustomsValue($customsValue);
        $request->setFreeMethodWeight(0);
        $request->setDhlShipmentType($request->getPackagingType());

        $request->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Object $request
     * @return \Magento\Object
     */
    protected function _doShipmentRequest(\Magento\Object $request)
    {
        $this->_prepareShipmentRequest($request);
        $request->setAction('GenerateLabel');
        $this->_mapRequestToShipment($request);
        $this->setRequest($request);

        return $this->_doRequest();
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Object|null $params
     * @return array|bool
     */
    public function getContainerTypes(\Magento\Object $params = null)
    {
        return $this->getCode('shipment_type');
    }
}
