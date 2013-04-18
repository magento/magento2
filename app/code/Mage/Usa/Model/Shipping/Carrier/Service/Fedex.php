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
 * @category    Mage
 * @package     Mage_Usa
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Usa_Model_Shipping_Carrier_Service_Fedex implements Mage_Shipping_Model_Carrier_Service_Interface
{

    const USA_COUNTRY_ID = 'US';
    const CANADA_COUNTRY_ID = 'CA';
    const MEXICO_COUNTRY_ID = 'MX';

    const HANDLING_TYPE_PERCENT = 'P';
    const HANDLING_TYPE_FIXED = 'F';

    const HANDLING_ACTION_PERPACKAGE = 'P';
    const HANDLING_ACTION_PERORDER = 'O';

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
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     * @var array
     */
    protected $_request = null;

    /**
     * Raw rate request data
     *
     * @var Varien_Object|null
     */
    protected $_rawRequest = null;

    /**
     * Rate result data
     * @var array
     */
    protected $_result = null;

    /**
     * Path to wsdl file of rate service
     *
     * @var string
     */
    protected $_rateServiceWsdl;

    /**
     * Container types that could be customized for FedEx carrier
     *
     * @var array
     */
    protected $_customizableContainerTypes = array('YOUR_PACKAGING');

    public function __construct()
    {
        $wsdlBasePath = Mage::getModuleDir('etc', 'Mage_Usa')  . DS . 'wsdl' . DS . 'FedEx' . DS;
        $this->_rateServiceWsdl = $wsdlBasePath . 'RateService_v10.wsdl';
    }

    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool $sandboxMode
     * @param bool|int $trace
     * @return SoapClient
     */
    protected function _createSoapClient($wsdl, $sandboxMode, $trace = false )
    {
        $client = new SoapClient($wsdl, array('trace' => $trace));
        $client->__setLocation($sandboxMode
            ? 'https://wsbeta.fedex.com:443/web-services/rate'
            : 'https://ws.fedex.com:443/web-services/rate'
        );

        return $client;
    }

    /**
     * Create rate soap client
     * @param bool $sandboxMode
     * @return SoapClient
     */
    protected function _createRateSoapClient($sandboxMode)
    {
        return $this->_createSoapClient($this->_rateServiceWsdl, $sandboxMode);
    }

    /**
     * @Type call
     * @Consumes(schema="http://www.magento.com/schemas/shippingrate-input.xsd", bundle="consumer-ux")
     * @Produces(schema="http://www.magento.com/schemas/shippingrate-output.xsd", bundle="consumer-ux")
     */
    public function getRates(array $shippingRateInput)
    {
        $this->setRequest($shippingRateInput);
        $this->_result = $this->_getQuotes();
        $this->_updateFreeMethodQuote($shippingRateInput);
        return $this->getResult();
    }

    /**
     * Prepare and set request to this instance
     *
     * @param array $request
     * @return Mage_Usa_Model_Shipping_Carrier_Service_Fedex
     */
    public function setRequest(array $request)
    {
        $this->_request = $request;
        $configuration = $request['carrierConfiguration'];
        $rateRequest = $request['rateRequest'];
        $r = new Varien_Object();

        if ($rateRequest['limitMethod']) {
            $r->setService($rateRequest->getLimitMethod());
        }

        $r->setAccount($configuration['account']);
        $r->setDropoffType($configuration['dropoff']);
        $r->setPackaging($configuration['packaging']);
        $r->setOrigCountry($rateRequest['origin']['countryId']);
        $r->setOrigPostal($rateRequest['origin']['postcode']);
        if ($rateRequest['destination']['countryId']) {
            $destCountry = $rateRequest['destination']['countryId'];
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry($destCountry);
        $r->setDestPostal($rateRequest['destination']['postcode']);
        $weight = $this->_getTotalNumOfBoxes($rateRequest['packageWeight']);
        $r->setWeight($weight);
        if ($rateRequest['freeMethodWeight'] != $rateRequest['packageWeight']) {
            $r->setFreeMethodWeight($rateRequest['freeMethodWeight']);
        }
        $r->setValue($rateRequest['packagePhysicalValue']);
        $r->setValueWithDiscount($rateRequest['packageValueWithDiscount']);
        $r->setMeterNumber($configuration['meterNumber']);
        $r->setKey($configuration['key']);
        $r->setPassword($configuration['password']);
//        $r->setIsReturn($rateRequest->getIsReturn());
        $r->setBaseSubtotalInclTax($rateRequest['baseSubtotalWithTax']);

        $this->_rawRequest = $r;

        return $this;
    }

    /**
     * @return array|null
     */
    protected function _getRequest()
    {
        return $this->_request;
    }

    /**
     * @param string $field
     * @return mixed
     */
    protected function _getConfigData($field)
    {
        $request=$this->_getRequest();
        $configuration = $request['carrierConfiguration'];
        return $configuration[$field];
    }

    /**
     * Get result of request
     *
     * @return array
     */
    public function getResult()
    {
       return $this->_result;
    }

    /**
     * Get version of rates request
     *
     * @return array
     */
    public function getVersionInfo()
    {
        return array(
            'ServiceId'    => 'crs',
            'Major'        => '10',
            'Intermediate' => '0',
            'Minor'        => '0'
        );
    }

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     * @return array
     */
    protected function _formRateRequest($purpose)
    {
        $r = $this->_rawRequest;
        $rValue = $r->getValue();
        $ratesRequest = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key'      => $r->getKey(),
                    'Password' => $r->getPassword()
                )
            ),
            'ClientDetail' => array(
                'AccountNumber' => $r->getAccount(),
                'MeterNumber'   => $r->getMeterNumber()
            ),
            'Version' => $this->getVersionInfo(),
            'RequestedShipment' => array(
                'DropoffType'   => $r->getDropoffType(),
                'ShipTimestamp' => date('c'),
                'PackagingType' => $r->getPackaging(),
                'TotalInsuredValue' => array(
                    'Amount'  => $rValue['amount'],
                    'Currency' => $rValue['currencyCode']
                ),
                'Shipper' => array(
                    'Address' => array(
                        'PostalCode'  => $r->getOrigPostal(),
                        'CountryCode' => $r->getOrigCountry()
                    )
                ),
                'Recipient' => array(
                    'Address' => array(
                        'PostalCode'  => $r->getDestPostal(),
                        'CountryCode' => $r->getDestCountry(),
                        'Residential' => (bool)$this->_getConfigData('residenceDelivery')
                    )
                ),
                'ShippingChargesPayment' => array(
                    'PaymentType' => 'SENDER',
                    'Payor' => array(
                        'AccountNumber' => $r->getAccount(),
                        'CountryCode'   => $r->getOrigCountry()
                    )
                ),
                'CustomsClearanceDetail' => array(
                    'CustomsValue' => array(
                        'Amount' => $rValue['amount'],
                        'Currency' => $rValue['currencyCode']
                    )
                ),
                'RateRequestTypes' => 'LIST',
                'PackageCount'     => '1',
                'PackageDetail'    => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' => array(
                    '0' => array(
                        'Weight' => array(
                            'Value' => (float)$r->getWeight(),
                            'Units' => 'LB'
                        ),
                        'GroupPackageCount' => 1,
                    )
                )
            )
        );

        if ($purpose == self::RATE_REQUEST_GENERAL) {
            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][0]['InsuredValue'] = array(
                'Amount'  => $rValue['amount'],
                'Currency' => $rValue['currencyCode']
            );
        } else if ($purpose == self::RATE_REQUEST_SMARTPOST) {
            $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
            $ratesRequest['RequestedShipment']['SmartPostDetail'] = array(
                'Indicia' => ((float)$r->getWeight() >= 1) ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                'HubId' => $this->_getConfigData('smartpostHubid')
            );
        }

        return $ratesRequest;
    }

    /**
     * Makes remote request to the carrier and returns a response
     *
     * @param string $purpose
     * @return mixed
     */
    protected function _doRatesRequest($purpose)
    {
        $ratesRequest = $this->_formRateRequest($purpose);
        $client = $this->_createRateSoapClient($this->_getConfigData('sandboxMode'));
        return $client->getRates($ratesRequest);
    }

    /**
     * Do remote request for and handle errors
     *
     * @return array
     */
    protected function _getQuotes()
    {
        $result = array();
        // make separate request for Smart Post method
        $allowedMethods = explode(',', $this->_getConfigData('allowedMethods'));
        if (in_array(self::RATE_REQUEST_SMARTPOST, $allowedMethods)) {
            $response = $this->_doRatesRequest(self::RATE_REQUEST_SMARTPOST);
            $responseResult = $this->_prepareRateResponse($response);
            $result = $this->_resultMerge($result, $responseResult);
        }
        // make general request for all methods
        $response = $this->_doRatesRequest(self::RATE_REQUEST_GENERAL);
        $responseResult = $this->_prepareRateResponse($response);
        $result = $this->_resultMerge($result, $responseResult);
        return $result;
    }

    /**
     * @param array $result1
     * @param array $result2
     * @return array
     */
    protected function _resultMerge($result1, $result2)
    {
        foreach($result2['methodError'] as $error) {
            $result1['shippingMethodError'][] = $error;
        }
        foreach($result2['method'] as $method) {
            $result1['shippingMethods'][] = $method;
        }
        return $result1;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return array
     */
    protected function _prepareRateResponse($response)
    {
        $costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve tracking';

        if (is_object($response)) {
            if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
                $errorTitle = (string)$response->Notifications->Message;
            } elseif (isset($response->RateReplyDetails)) {
                $allowedMethods = explode(",", $this->_getConfigData('allowedMethods'));

                if (is_array($response->RateReplyDetails)) {
                    foreach ($response->RateReplyDetails as $rate) {
                        $serviceName = (string)$rate->ServiceType;
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = $this->_getRateAmountOriginBased($rate);
                            $costArr[$serviceName]  = $amount;
                            $priceArr[$serviceName] = $this->_getMethodPrice($amount, $serviceName);
                        }
                    }
                    asort($priceArr);
                } else {
                    $rate = $response->RateReplyDetails;
                    $serviceName = (string)$rate->ServiceType;
                    if (in_array($serviceName, $allowedMethods)) {
                        $amount = $this->_getRateAmountOriginBased($rate);
                        $costArr[$serviceName]  = $amount;
                        $priceArr[$serviceName] = $this->_getMethodPrice($amount, $serviceName);
                    }
                }
            }
        }

        $result = array();
        if (empty($priceArr)) {
            $error = array();
            $error['carrier'] = $this->_code;
            $error['carrierTitle'] = $this->_getConfigData('title');
            $error['errorMessage'] = $errorTitle;
            $error['errorMessage'] = $this->_getConfigData('specificerrmsg');
            $result['methodError'][] = $error;
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = array();
                $rate['carrier'] = $this->_code;
                $rate['carrierTitle'] = $this->_getConfigData('title');
                $rate['method'] = $method;
                $rate['methodTitle'] = $this->getCode('method', $method);
                $rate['cost'] = $costArr[$method];
                $rate['price'] = $price;
                $result['method'][] = $rate;
            }
        }
        return $result;
    }

    /**
     * Calculate price considering free shipping and handling fee
     *
     * @param string $cost
     * @param string $method
     * @return float|string
     */
    protected function _getMethodPrice($cost, $method = '')
    {
        $baseSubtotalInclTax = $this->_rawRequest->getBaseSubtotalInclTax();
        return $method == $this->_getConfigData('freeMethod')
            && (!(bool)$this->_getConfigData('freeShippingEnabled')
                || $this->_getConfigData('freeShippingSubtotal') <= $baseSubtotalInclTax['amount'])
            ? '0.00'
            : $this->_getFinalPriceWithHandlingFee($cost);
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|bool
     */
    public function getCode($type, $code='')
    {
        $codes = array(
            'method' => array(
                'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => Mage::helper('Mage_Usa_Helper_Data')->__('Europe First Priority'),
                'FEDEX_1_DAY_FREIGHT'                 => Mage::helper('Mage_Usa_Helper_Data')->__('1 Day Freight'),
                'FEDEX_2_DAY_FREIGHT'                 => Mage::helper('Mage_Usa_Helper_Data')->__('2 Day Freight'),
                'FEDEX_2_DAY'                         => Mage::helper('Mage_Usa_Helper_Data')->__('2 Day'),
                'FEDEX_2_DAY_AM'                      => Mage::helper('Mage_Usa_Helper_Data')->__('2 Day AM'),
                'FEDEX_3_DAY_FREIGHT'                 => Mage::helper('Mage_Usa_Helper_Data')->__('3 Day Freight'),
                'FEDEX_EXPRESS_SAVER'                 => Mage::helper('Mage_Usa_Helper_Data')->__('Express Saver'),
                'FEDEX_GROUND'                        => Mage::helper('Mage_Usa_Helper_Data')->__('Ground'),
                'FIRST_OVERNIGHT'                     => Mage::helper('Mage_Usa_Helper_Data')->__('First Overnight'),
                'GROUND_HOME_DELIVERY'                => Mage::helper('Mage_Usa_Helper_Data')->__('Home Delivery'),
                'INTERNATIONAL_ECONOMY'               => Mage::helper('Mage_Usa_Helper_Data')->__('International Economy'),
                'INTERNATIONAL_ECONOMY_FREIGHT'       => Mage::helper('Mage_Usa_Helper_Data')->__('Intl Economy Freight'),
                'INTERNATIONAL_FIRST'                 => Mage::helper('Mage_Usa_Helper_Data')->__('International First'),
                'INTERNATIONAL_GROUND'                => Mage::helper('Mage_Usa_Helper_Data')->__('International Ground'),
                'INTERNATIONAL_PRIORITY'              => Mage::helper('Mage_Usa_Helper_Data')->__('International Priority'),
                'INTERNATIONAL_PRIORITY_FREIGHT'      => Mage::helper('Mage_Usa_Helper_Data')->__('Intl Priority Freight'),
                'PRIORITY_OVERNIGHT'                  => Mage::helper('Mage_Usa_Helper_Data')->__('Priority Overnight'),
                'SMART_POST'                          => Mage::helper('Mage_Usa_Helper_Data')->__('Smart Post'),
                'STANDARD_OVERNIGHT'                  => Mage::helper('Mage_Usa_Helper_Data')->__('Standard Overnight'),
                'FEDEX_FREIGHT'                       => Mage::helper('Mage_Usa_Helper_Data')->__('Freight'),
                'FEDEX_NATIONAL_FREIGHT'              => Mage::helper('Mage_Usa_Helper_Data')->__('National Freight'),
            ),
            'dropoff' => array(
                'REGULAR_PICKUP'          => Mage::helper('Mage_Usa_Helper_Data')->__('Regular Pickup'),
                'REQUEST_COURIER'         => Mage::helper('Mage_Usa_Helper_Data')->__('Request Courier'),
                'DROP_BOX'                => Mage::helper('Mage_Usa_Helper_Data')->__('Drop Box'),
                'BUSINESS_SERVICE_CENTER' => Mage::helper('Mage_Usa_Helper_Data')->__('Business Service Center'),
                'STATION'                 => Mage::helper('Mage_Usa_Helper_Data')->__('Station')
            ),
            'packaging' => array(
                'FEDEX_ENVELOPE' => Mage::helper('Mage_Usa_Helper_Data')->__('FedEx Envelope'),
                'FEDEX_PAK'      => Mage::helper('Mage_Usa_Helper_Data')->__('FedEx Pak'),
                'FEDEX_BOX'      => Mage::helper('Mage_Usa_Helper_Data')->__('FedEx Box'),
                'FEDEX_TUBE'     => Mage::helper('Mage_Usa_Helper_Data')->__('FedEx Tube'),
                'FEDEX_10KG_BOX' => Mage::helper('Mage_Usa_Helper_Data')->__('FedEx 10kg Box'),
                'FEDEX_25KG_BOX' => Mage::helper('Mage_Usa_Helper_Data')->__('FedEx 25kg Box'),
                'YOUR_PACKAGING' => Mage::helper('Mage_Usa_Helper_Data')->__('Your Packaging')
            ),
            'containers_filter' => array(
                array(
                    'containers' => array('FEDEX_ENVELOPE', 'FEDEX_PAK'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'FEDEX_EXPRESS_SAVER',
                                'FEDEX_2_DAY',
                                'FEDEX_2_DAY_AM',
                                'STANDARD_OVERNIGHT',
                                'PRIORITY_OVERNIGHT',
                                'FIRST_OVERNIGHT',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'INTERNATIONAL_FIRST',
                                'INTERNATIONAL_ECONOMY',
                                'INTERNATIONAL_PRIORITY',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('FEDEX_BOX', 'FEDEX_TUBE'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
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
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'INTERNATIONAL_FIRST',
                                'INTERNATIONAL_ECONOMY',
                                'INTERNATIONAL_PRIORITY',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('FEDEX_10KG_BOX', 'FEDEX_25KG_BOX'),
                    'filters'    => array(
                        'within_us' => array(),
                        'from_us' => array('method' => array('INTERNATIONAL_PRIORITY'))
                    )
                ),
                array(
                    'containers' => array('YOUR_PACKAGING'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' =>array(
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
                            )
                        ),
                        'from_us' => array(
                            'method' =>array(
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
                            )
                        )
                    )
                )
            ),

            'delivery_confirmation_types' => array(
                'NO_SIGNATURE_REQUIRED' => Mage::helper('Mage_Usa_Helper_Data')->__('Not Required'),
                'ADULT'                 => Mage::helper('Mage_Usa_Helper_Data')->__('Adult'),
                'DIRECT'                => Mage::helper('Mage_Usa_Helper_Data')->__('Direct'),
                'INDIRECT'              => Mage::helper('Mage_Usa_Helper_Data')->__('Indirect'),
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
     * Get the handling fee for the shipping + cost
     *
     * @param float $cost
     * @return float final price for shipping method
     */
    protected function _getFinalPriceWithHandlingFee($cost)
    {
        $handlingFee = $this->_getConfigData('handlingFee');
        $handlingType = $this->_getConfigData('handlingType');
        if (!$handlingType) {
            $handlingType = self::HANDLING_TYPE_FIXED;
        }
        $handlingAction = $this->_getConfigData('handlingAction');
        if (!$handlingAction) {
            $handlingAction = self::HANDLING_ACTION_PERORDER;
        }

        return $handlingAction == self::HANDLING_ACTION_PERPACKAGE
            ? $this->_getPerpackagePrice($cost, $handlingType, $handlingFee)
            : $this->_getPerorderPrice($cost, $handlingType, $handlingFee);
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
            return ($cost * $this->_numBoxes) + ($cost * $this->_numBoxes * $handlingFee / 100);
        }

        return ($cost * $this->_numBoxes) + $handlingFee;
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
        if ($handlingType == self::HANDLING_TYPE_PERCENT) {
            return ($cost + ($cost * $handlingFee/100)) * $this->_numBoxes;
        }

        return ($cost + $handlingFee) * $this->_numBoxes;
    }

    /**
     * Get origin based amount form response of rate estimation
     *
     * @param stdClass $rate
     * @return null|float
     */
    protected function _getRateAmountOriginBased($rate)
    {
        $amount = null;
        $rateTypeAmounts = array();

        if (is_object($rate)) {
            // The "RATED..." rates are expressed in the currency of the origin country
            foreach ($rate->RatedShipmentDetails as $ratedShipmentDetail) {
                $netAmount = (string)$ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount;
                $rateType = (string)$ratedShipmentDetail->ShipmentRateDetail->RateType;
                $rateTypeAmounts[$rateType] = $netAmount;
            }

            // Order is important
            foreach (array('RATED_ACCOUNT_SHIPMENT', 'RATED_LIST_SHIPMENT', 'RATED_LIST_PACKAGE') as $rateType) {
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
     * @param array $request
     * @return null
     */
    protected function _updateFreeMethodQuote($request)
    {
        if (($request['freeMethodWeight'] == $request['packageWeight']) || empty($request['freeMethodWeight'])) {
            return;
        }

        $freeMethod = $this->_getConfigData('freeMethod');
        if (!$freeMethod) {
            return;
        }
        $freeRateId = false;

        foreach ($this->_result['method'] as $i=>$item) {
            if ($item['method'] == $freeMethod) {
                $freeRateId = $i;
                break;
            }
        }

        if ($freeRateId === false) {
            return;
        }
        $price = null;
        if ($request['freeMethodWeight'] > 0) {
            $this->_setFreeMethodRequest($freeMethod);

            $result = $this->_getQuotes();
            if ($result && ($rates = $result['method']) && count($rates)>0) {
                if (count($rates) == 1) {
                    $price = $rates[0]['price'];
                }
                if (count($rates) > 1) {
                    foreach ($rates as $rate) {
                        if ($rate['method'] == $freeMethod
                        ) {
                            $price = $rate['price'];
                        }
                    }
                }
            }
        } else {
            /**
             * if we can apply free shipping for all order we should force price
             * to $0.00 for shipping with out sending second request to carrier
             */
            $price = 0;
        }

        /**
         * if we did not get our free shipping method in response we must use its old price
         */
        if (!is_null($price)) {
            $this->_result['method'][$freeRateId]['price'] = $price;
        }
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
        $weight = $this->_getTotalNumOfBoxes($r->getFreeMethodWeight());
        $r->setWeight($weight);
        $r->setService($freeMethod);
    }


    /**
     * set the number of boxes for shipping
     *
     * @return weight
     */
    protected function _getTotalNumOfBoxes($weight)
    {
        /*
        reset num box first before retrieve again
        */
        $this->_numBoxes = 1;
        $weight = $this->_convertWeightToLbs($weight);
        $maxPackageWeight = $this->_getConfigData('maxPackageWeight');
        if ($weight > $maxPackageWeight && $maxPackageWeight != 0) {
            $this->_numBoxes = ceil($weight/$maxPackageWeight);
            $weight = $weight/$this->_numBoxes;
        }
        return $weight;
    }

    /**
     *  Return weight in pounds
     *
     *  @param integer Weight in someone measure
     *  @return float Weight in pounds
     */
    protected function _convertWeightToLbs($weight)
    {
        return $weight;
    }
}
