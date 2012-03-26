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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * UPS shipping implementation
 *
 * @category   Mage
 * @package    Mage_Usa
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Usa_Model_Shipping_Carrier_Ups
    extends Mage_Usa_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'ups';

    /**
     * Delivery Confirmation level based on origin/destination
     *
     * @var int
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
     * @var Mage_Shipping_Model_Rate_Request|null
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
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $_result = null;

    /**
     * Base currency rate
     *
     * @var double
     */
    protected $_baseCurrencyRate;

    /**
     * Xml access request
     *
     * @var string
     */
    protected $_xmlAccessRequest = null;

    /**
     * Default cgi gateway url
     *
     * @var string
     */
    protected $_defaultCgiGatewayUrl = 'http://www.ups.com:80/using/services/rave/qcostcgi.cgi';

    /**
     * Default urls for shipment
     *
     * @var array
     */
    protected $_defaultUrls = array(
        'ShipConfirm' => 'https://wwwcie.ups.com/ups.app/xml/ShipConfirm',
        'ShipAccept'  => 'https://wwwcie.ups.com/ups.app/xml/ShipAccept',
    );

    /**
     * Container types that could be customized for UPS carrier
     *
     * @var array
     */
    protected $_customizableContainerTypes = array('CP', 'CSP');

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag($this->_activeFlag)) {
            return false;
        }

        $this->setRequest($request);

        $this->_result = $this->_getQuotes();
        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * Prepare and set request to this instance
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Usa_Model_Shipping_Carrier_Ups
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setAction($this->getCode('action', 'single'));
            $r->setProduct($request->getLimitMethod());
        } else {
            $r->setAction($this->getCode('action', 'all'));
            $r->setProduct('GND'.$this->getConfigData('dest_type'));
        }

        if ($request->getUpsPickup()) {
            $pickup = $request->getUpsPickup();
        } else {
            $pickup = $this->getConfigData('pickup');
        }
        $r->setPickup($this->getCode('pickup', $pickup));

        if ($request->getUpsContainer()) {
            $container = $request->getUpsContainer();
        } else {
            $container = $this->getConfigData('container');
        }
        $r->setContainer($this->getCode('container', $container));

        if ($request->getUpsDestType()) {
            $destType = $request->getUpsDestType();
        } else {
            $destType = $this->getConfigData('dest_type');
        }
        $r->setDestType($this->getCode('dest_type', $destType));

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $request->getStoreId()
            );
        }

        $r->setOrigCountry(Mage::getModel('Mage_Directory_Model_Country')->load($origCountry)->getIso2Code());

        if ($request->getOrigRegionCode()) {
            $origRegionCode = $request->getOrigRegionCode();
        } else {
            $origRegionCode = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID,
                $request->getStoreId()
            );
        }
        if (is_numeric($origRegionCode)) {
            $origRegionCode = Mage::getModel('Mage_Directory_Model_Region')->load($origRegionCode)->getCode();
        }
        $r->setOrigRegionCode($origRegionCode);

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP,
                $request->getStoreId()
            ));
        }

        if ($request->getOrigCity()) {
            $r->setOrigCity($request->getOrigCity());
        } else {
            $r->setOrigCity(Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY,
                $request->getStoreId()
            ));
        }


        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        //for UPS, puero rico state for US will assume as puerto rico country
        if ($destCountry == self::USA_COUNTRY_ID
            && ($request->getDestPostcode()=='00912' || $request->getDestRegionCode()==self::PUERTORICO_COUNTRY_ID)
        ) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        // For UPS, Guam state of the USA will be represented by Guam country
        if ($destCountry == self::USA_COUNTRY_ID && $request->getDestRegionCode() == self::GUAM_REGION_CODE) {
            $destCountry = self::GUAM_COUNTRY_ID;
        }

        $r->setDestCountry(Mage::getModel('Mage_Directory_Model_Country')->load($destCountry)->getIso2Code());

        $r->setDestRegionCode($request->getDestRegionCode());

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        } else {

        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());

        $weight = $this->_getCorrectWeight($weight);

        $r->setWeight($weight);
        if ($request->getFreeMethodWeight()!=$request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackageValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        if ($request->getUpsUnitMeasure()) {
            $unit = $request->getUpsUnitMeasure();
        } else {
            $unit = $this->getConfigData('unit_of_measure');
        }
        $r->setUnitMeasure($unit);

        $r->setIsReturn($request->getIsReturn());

        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->_rawRequest = $r;

        return $this;
    }

    /**
     * Get correct weigt.
     *
     * Namely:
     * Checks the current weight to comply with the minimum weight standards set by the carrier.
     * Then strictly rounds the weight up until the first significant digit after the decimal point.
     *
     * @param float|integer|double $weight
     * @return float
     */
    protected function _getCorrectWeight($weight)
    {
        $minWeight = $this->getConfigData('min_package_weight');

        if($weight < $minWeight){
            $weight = $minWeight;
        }

        //rounds a number to one significant figure
        $weight = ceil($weight*10) / 10;

        return $weight;
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
     * Do remote request for  and handle errors
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getQuotes()
    {
        switch ($this->getConfigData('type')) {
            case 'UPS':
               return $this->_getCgiQuotes();

            case 'UPS_XML':
               return $this->_getXmlQuotes();
        }
        return null;
    }

    /**
     * Set free method request
     *
     * @param string $freeMethod
     * @return null
     */
    protected function _setFreeMethodRequest($freeMethod)
    {
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $weight = $this->_getCorrectWeight($weight);
        $r->setWeight($weight);
        $r->setAction($this->getCode('action', 'single'));
        $r->setProduct($freeMethod);
    }

    /**
     * Get cgi rates
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getCgiQuotes()
    {
        $r = $this->_rawRequest;

        $params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            'origCity'       => $r->getOrigCity(),
            '19_destPostal'  => Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $r->getDestCountry() ?
                substr($r->getDestPostal(), 0, 5) :
                $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
            'weight_std'     => strtolower($r->getUnitMeasure()),
        );
        $params['47_rate_chart'] = $params['47_rate_chart']['label'];

        $responseBody = $this->_getCachedQuotes($params);
        if ($responseBody === null) {
            $debugData = array('request' => $params);
            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultCgiGatewayUrl;
                }
                $client = new Zend_Http_Client();
                $client->setUri($url);
                $client->setConfig(array('maxredirects'=>0, 'timeout'=>30));
                $client->setParameterGet($params);
                $response = $client->request();
                $responseBody = $response->getBody();

                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($params, $responseBody);
            }
            catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
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
    public function getShipmentByCode($code, $origin = null){
        if($origin===null){
            $origin = $this->getConfigData('origin_shipment');
        }
        $arr = $this->getCode('originShipment',$origin);
        if(isset($arr[$code]))
            return $arr[$code];
        else
            return false;
    }


    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _parseCgiResponse($response)
    {
        $costArr = array();
        $priceArr = array();
        $errorTitle = Mage::helper('Mage_Usa_Helper_Data')->__('Unknown error');
        if (strlen(trim($response))>0) {
            $rRows = explode("\n", $response);
            $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
            foreach ($rRows as $rRow) {
                $r = explode('%', $rRow);
                switch (substr($r[0],-1)) {
                    case 3: case 4:
                        if (in_array($r[1], $allowedMethods)) {
                            $responsePrice = Mage::app()->getLocale()->getNumber($r[8]);
                            $costArr[$r[1]] = $responsePrice;
                            $priceArr[$r[1]] = $this->getMethodPrice($responsePrice, $r[1]);
                        }
                        break;
                    case 5:
                        $errorTitle = $r[1];
                        break;
                    case 6:
                        if (in_array($r[3], $allowedMethods)) {
                            $responsePrice = Mage::app()->getLocale()->getNumber($r[10]);
                            $costArr[$r[3]] = $responsePrice;
                            $priceArr[$r[3]] = $this->getMethodPrice($responsePrice, $r[3]);
                        }
                        break;
                }
            }
            asort($priceArr);
        }

        $result = Mage::getModel('Mage_Shipping_Model_Rate_Result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('Mage_Shipping_Model_Rate_Result_Error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('Mage_Shipping_Model_Rate_Result_Method');
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $method_arr = $this->getCode('method', $method);
                $rate->setMethodTitle(Mage::helper('Mage_Usa_Helper_Data')->__($method_arr));
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
     * @return array|bool
     */
    public function getCode($type, $code='')
    {
        $codes = array(
            'action'=>array(
                'single'=>'3',
                'all'=>'4',
            ),

            'originShipment'=>array(
                // United States Domestic Shipments
                'United States Domestic Shipments' => array(
                    '01' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Next Day Air'),
                    '02' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Second Day Air'),
                    '03' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Ground'),
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Standard'),
                    '12' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Three-Day Select'),
                    '13' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Next Day Air Saver'),
                    '14' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express Plus'),
                    '59' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Second Day Air A.M.'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Saver'),
                ),
                // Shipments Originating in United States
                'Shipments Originating in United States' => array(
                    '01' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Next Day Air'),
                    '02' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Second Day Air'),
                    '03' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Ground'),
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Standard'),
                    '12' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Three-Day Select'),
                    '14' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express Plus'),
                    '59' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Second Day Air A.M.'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Saver'),
                ),
                // Shipments Originating in Canada
                'Shipments Originating in Canada' => array(
                    '01' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express'),
                    '02' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Expedited'),
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Standard'),
                    '12' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Three-Day Select'),
                    '14' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express Early A.M.'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Saver'),
                ),
                // Shipments Originating in the European Union
                'Shipments Originating in the European Union' => array(
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Expedited'),
                    '11' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Standard'),
                    '54' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express PlusSM'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Saver'),
                ),
                // Polish Domestic Shipments
                'Polish Domestic Shipments' => array(
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Expedited'),
                    '11' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Standard'),
                    '54' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Saver'),
                    '82' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Today Standard'),
                    '83' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Today Dedicated Courrier'),
                    '84' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Today Intercity'),
                    '85' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Today Express'),
                    '86' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Today Express Saver'),
                ),
                // Puerto Rico Origin
                'Puerto Rico Origin' => array(
                    '01' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Next Day Air'),
                    '02' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Second Day Air'),
                    '03' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Ground'),
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Expedited'),
                    '14' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Saver'),
                ),
                // Shipments Originating in Mexico
                'Shipments Originating in Mexico' => array(
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Expedited'),
                    '54' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express Plus'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Saver'),
                ),
                // Shipments Originating in Other Countries
                'Shipments Originating in Other Countries' => array(
                    '07' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express'),
                    '08' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Standard'),
                    '54' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Saver')
                )
            ),

            'method'=>array(
                '1DM'    => Mage::helper('Mage_Usa_Helper_Data')->__('Next Day Air Early AM'),
                '1DML'   => Mage::helper('Mage_Usa_Helper_Data')->__('Next Day Air Early AM Letter'),
                '1DA'    => Mage::helper('Mage_Usa_Helper_Data')->__('Next Day Air'),
                '1DAL'   => Mage::helper('Mage_Usa_Helper_Data')->__('Next Day Air Letter'),
                '1DAPI'  => Mage::helper('Mage_Usa_Helper_Data')->__('Next Day Air Intra (Puerto Rico)'),
                '1DP'    => Mage::helper('Mage_Usa_Helper_Data')->__('Next Day Air Saver'),
                '1DPL'   => Mage::helper('Mage_Usa_Helper_Data')->__('Next Day Air Saver Letter'),
                '2DM'    => Mage::helper('Mage_Usa_Helper_Data')->__('2nd Day Air AM'),
                '2DML'   => Mage::helper('Mage_Usa_Helper_Data')->__('2nd Day Air AM Letter'),
                '2DA'    => Mage::helper('Mage_Usa_Helper_Data')->__('2nd Day Air'),
                '2DAL'   => Mage::helper('Mage_Usa_Helper_Data')->__('2nd Day Air Letter'),
                '3DS'    => Mage::helper('Mage_Usa_Helper_Data')->__('3 Day Select'),
                'GND'    => Mage::helper('Mage_Usa_Helper_Data')->__('Ground'),
                'GNDCOM' => Mage::helper('Mage_Usa_Helper_Data')->__('Ground Commercial'),
                'GNDRES' => Mage::helper('Mage_Usa_Helper_Data')->__('Ground Residential'),
                'STD'    => Mage::helper('Mage_Usa_Helper_Data')->__('Canada Standard'),
                'XPR'    => Mage::helper('Mage_Usa_Helper_Data')->__('Worldwide Express'),
                'WXS'    => Mage::helper('Mage_Usa_Helper_Data')->__('Worldwide Express Saver'),
                'XPRL'   => Mage::helper('Mage_Usa_Helper_Data')->__('Worldwide Express Letter'),
                'XDM'    => Mage::helper('Mage_Usa_Helper_Data')->__('Worldwide Express Plus'),
                'XDML'   => Mage::helper('Mage_Usa_Helper_Data')->__('Worldwide Express Plus Letter'),
                'XPD'    => Mage::helper('Mage_Usa_Helper_Data')->__('Worldwide Expedited'),
            ),

            'pickup'=>array(
                'RDP'    => array("label"=>'Regular Daily Pickup',"code"=>"01"),
                'OCA'    => array("label"=>'On Call Air',"code"=>"07"),
                'OTP'    => array("label"=>'One Time Pickup',"code"=>"06"),
                'LC'     => array("label"=>'Letter Center',"code"=>"19"),
                'CC'     => array("label"=>'Customer Counter',"code"=>"03"),
            ),

            'container'=>array(
                'CP'     => '00', // Customer Packaging
                'ULE'    => '01', // UPS Letter Envelope
                'CSP'    => '02', // Customer Supplied Package
                'UT'     => '03', // UPS Tube
                'PAK'    => '04', // PAK
                'UEB'    => '21', // UPS Express Box
                'UW25'   => '24', // UPS Worldwide 25 kilo
                'UW10'   => '25', // UPS Worldwide 10 kilo
                'PLT'    => '30', // Pallet
                'SEB'    => '2a', // Small Express Box
                'MEB'    => '2b', // Medium Express Box
                'LEB'    => '2c', // Large Express Box
            ),

            'container_description'=>array(
                'CP'     => Mage::helper('Mage_Usa_Helper_Data')->__('Customer Packaging'),
                'ULE'    => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Letter Envelope'),
                'CSP'    => Mage::helper('Mage_Usa_Helper_Data')->__('Customer Supplied Package'),
                'UT'     => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Tube'),
                'PAK'    => Mage::helper('Mage_Usa_Helper_Data')->__('PAK'),
                'UEB'    => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Express Box'),
                'UW25'   => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide 25 kilo'),
                'UW10'   => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide 10 kilo'),
                'PLT'    => Mage::helper('Mage_Usa_Helper_Data')->__('Pallet'),
                'SEB'    => Mage::helper('Mage_Usa_Helper_Data')->__('Small Express Box'),
                'MEB'    => Mage::helper('Mage_Usa_Helper_Data')->__('Medium Express Box'),
                'LEB'    => Mage::helper('Mage_Usa_Helper_Data')->__('Large Express Box'),
            ),

            'dest_type'=>array(
                'RES'    => '01', // Residential
                'COM'    => '02', // Commercial
            ),

            'dest_type_description'=>array(
                'RES'    => Mage::helper('Mage_Usa_Helper_Data')->__('Residential'),
                'COM'    => Mage::helper('Mage_Usa_Helper_Data')->__('Commercial'),
            ),

            'unit_of_measure'=>array(
                'LBS'   =>  Mage::helper('Mage_Usa_Helper_Data')->__('Pounds'),
                'KGS'   =>  Mage::helper('Mage_Usa_Helper_Data')->__('Kilograms'),
            ),
            'containers_filter' => array(
                array(
                    'containers' => array('00'), // Customer Packaging
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '12', // 3 Day Select
                                '59', // 2nd Day Air AM
                                '03', // Ground
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65', // Worldwide Saver
                                '11', // Standard
                            )
                        )
                    )
                ),
                array(
                    // Small Express Box, Medium Express Box, Large Express Box, UPS Tube
                    'containers' => array('2a', '2b', '2c', '03'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                                '13', // Next Day Air Saver
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65', // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('24', '25'), // UPS Worldwide 25 kilo, UPS Worldwide 10 kilo
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array()
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65', // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('01', '04'), // UPS Letter, UPS PAK
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                                '13', // Next Day Air Saver
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65', // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('04'), // UPS PAK
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array()
                        ),
                        'from_us' => array(
                            'method' => array(
                                '08', // Worldwide Expedited
                            )
                        )
                    )
                ),
            )
        );

        if (!isset($codes[$type])) {
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Get xml rates
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getXmlQuotes()
    {
        $url = $this->getConfigData('gateway_xml_url');

        $this->setXMLAccessRequest();
        $xmlRequest=$this->_xmlAccessRequest;

        $r = $this->_rawRequest;
        $params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            'origCity'       => $r->getOrigCity(),
            'origRegionCode' => $r->getOrigRegionCode(),
            '19_destPostal'  => Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $r->getDestCountry() ?
                substr($r->getDestPostal(), 0, 5) :
                $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            'destRegionCode' => $r->getDestRegionCode(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
        );

        if ($params['10_action'] == '4') {
            $params['10_action'] = 'Shop';
            $serviceCode = null; // Service code is not relevant when we're asking ALL possible services' rates
        } else {
            $params['10_action'] = 'Rate';
            $serviceCode = $r->getProduct() ? $r->getProduct() : '';
        }
        $serviceDescription = $serviceCode ? $this->getShipmentByCode($serviceCode) : '';

$xmlRequest .= <<< XMLRequest
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
            $xmlRequest .= "<Service>" .
                "<Code>{$serviceCode}</Code>" .
                "<Description>{$serviceDescription}</Description>" .
                "</Service>";
        }

      $xmlRequest .= <<< XMLRequest
      <Shipper>
XMLRequest;

        if ($this->getConfigFlag('negotiated_active') && ($shipper = $this->getConfigData('shipper_number')) ) {
            $xmlRequest .= "<ShipperNumber>{$shipper}</ShipperNumber>";
        }

        if ($r->getIsReturn()) {
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

$xmlRequest .= <<< XMLRequest
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

          $xmlRequest .= ($params['49_residential']==='01'
                  ? "<ResidentialAddressIndicator>{$params['49_residential']}</ResidentialAddressIndicator>"
                  : ''
          );

$xmlRequest .= <<< XMLRequest
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
      <PackagingType><Code>{$params['48_container']}</Code></PackagingType>
      <PackageWeight>
         <UnitOfMeasurement><Code>{$r->getUnitMeasure()}</Code></UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
    </Package>
XMLRequest;
        if ($this->getConfigFlag('negotiated_active')) {
            $xmlRequest .= "<RateInformation><NegotiatedRatesIndicator/></RateInformation>";
        }

$xmlRequest .= <<< XMLRequest
  </Shipment>
</RatingServiceSelectionRequest>
XMLRequest;

        $xmlResponse = $this->_getCachedQuotes($xmlRequest);
        if ($xmlResponse === null) {
            $debugData = array('request' => $xmlRequest);
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
                $xmlResponse = curl_exec ($ch);

                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            }
            catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
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
     * @return double
     */
    protected function _getBaseCurrencyRate($code)
    {
        if (!$this->_baseCurrencyRate) {
            $this->_baseCurrencyRate = Mage::getModel('Mage_Directory_Model_Currency')
                ->load($code)
                ->getAnyRate($this->_request->getBaseCurrency()->getCode());
        }

        return $this->_baseCurrencyRate;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _parseXmlResponse($xmlResponse)
    {
        $costArr = array();
        $priceArr = array();
        if (strlen(trim($xmlResponse))>0) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0];
            if ($success===1) {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                // Negotiated rates
                $negotiatedArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates");
                $negotiatedActive = $this->getConfigFlag('negotiated_active')
                    && $this->getConfigData('shipper_number')
                    && !empty($negotiatedArr);

                $allowedCurrencies = Mage::getModel('Mage_Directory_Model_Currency')->getConfigAllowCurrencies();

                foreach ($arr as $shipElement){
                    $code = (string)$shipElement->Service->Code;
                    if (in_array($code, $allowedMethods)) {

                        if ($negotiatedActive) {
                            $cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                        } else {
                            $cost = $shipElement->TotalCharges->MonetaryValue;
                        }

                        //convert price with Origin country currency code to base currency code
                        $successConversion = true;
                        $responseCurrencyCode = (string) $shipElement->TotalCharges->CurrencyCode;
                        if ($responseCurrencyCode) {
                            if (in_array($responseCurrencyCode, $allowedCurrencies)) {
                                $cost = (float) $cost * $this->_getBaseCurrencyRate($responseCurrencyCode);
                            } else {
                                $errorTitle = Mage::helper('Mage_Directory_Helper_Data')->__('Can\'t convert rate from "%s-%s".', $responseCurrencyCode, $this->_request->getPackageCurrency()->getCode());
                                $error = Mage::getModel('Mage_Shipping_Model_Rate_Result_Error');
                                $error->setCarrier('ups');
                                $error->setCarrierTitle($this->getConfigData('title'));
                                $error->setErrorMessage($errorTitle);
                                $successConversion = false;
                            }
                        }

                        if ($successConversion) {
                            $costArr[$code] = $cost;
                            $priceArr[$code] = $this->getMethodPrice(floatval($cost),$code);
                        }
                    }
                }
            } else {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
                $error = Mage::getModel('Mage_Shipping_Model_Rate_Result_Error');
                $error->setCarrier('ups');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            }
        }

        $result = Mage::getModel('Mage_Shipping_Model_Rate_Result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('Mage_Shipping_Model_Rate_Result_Error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            if(!isset($errorTitle)){
                $errorTitle = Mage::helper('Mage_Usa_Helper_Data')->__('Cannot retrieve shipping rates');
            }
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('Mage_Shipping_Model_Rate_Result_Method');
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $method_arr = $this->getShipmentByCode($method);
                $rate->setMethodTitle($method_arr);
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
     * @param mixed $trackings
     * @return mixed
     */
    public function getTracking($trackings)
    {
        $return = array();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }

        if ($this->getConfigData('type')=='UPS') {
            $this->_getCgiTracking($trackings);
        } elseif ($this->getConfigData('type')=='UPS_XML'){
            $this->setXMLAccessRequest();
            $this->_getXmlTracking($trackings);
        }

        return $this->_result;
    }

    /**
     * Set xml access request
     *
     * @return null
     */
    protected function setXMLAccessRequest()
    {
        $userid = $this->getConfigData('username');
        $userid_pass = $this->getConfigData('password');
        $access_key = $this->getConfigData('access_license_number');

        $this->_xmlAccessRequest =  <<<XMLAuth
<?xml version="1.0"?>
<AccessRequest xml:lang="en-US">
  <AccessLicenseNumber>$access_key</AccessLicenseNumber>
  <UserId>$userid</UserId>
  <Password>$userid_pass</Password>
</AccessRequest>
XMLAuth;
    }

    /**
     * Get cgi tracking
     *
     * @param mixed $trackings
     * @return mixed
     */
    protected function _getCgiTracking($trackings)
    {
        //ups no longer support tracking for data streaming version
        //so we can only reply the popup window to ups.
        $result = Mage::getModel('Mage_Shipping_Model_Tracking_Result');
        $defaults = $this->getDefaults();
        foreach($trackings as $tracking){
            $status = Mage::getModel('Mage_Shipping_Model_Tracking_Result_Status');
            $status->setCarrier('ups');
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("http://wwwapps.ups.com/WebTracking/processInputRequest?HTMLVersion=5.0&error_carried=true"
                . "&tracknums_displayed=5&TypeOfInquiryNumber=T&loc=en_US&InquiryNumber1=$tracking"
                . "&AgreeToTermsAndConditions=yes"
            );
            $result->append($status);
        }

        $this->_result = $result;
        return $result;
    }

    /**
     * Get xml tracking
     *
     * @param mixed $trackings
     * @return mixed
     */
    protected function _getXmlTracking($trackings)
    {
        $url = $this->getConfigData('tracking_xml_url');

        foreach($trackings as $tracking){
            $xmlRequest=$this->_xmlAccessRequest;

/*
* RequestOption==>'activity' or '1' to request all activities
*/
$xmlRequest .=  <<<XMLAuth
<?xml version="1.0" ?>
<TrackRequest xml:lang="en-US">
    <Request>
        <RequestAction>Track</RequestAction>
        <RequestOption>activity</RequestOption>
    </Request>
    <TrackingNumber>$tracking</TrackingNumber>
    <IncludeFreight>01</IncludeFreight>
</TrackRequest>
XMLAuth;
            $debugData = array('request' => $xmlRequest);

            try {
                $ch = curl_init();
                   curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $xmlResponse = curl_exec ($ch);
                $debugData['result'] = $xmlResponse;
                curl_close ($ch);
            }
            catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
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
     * @param string $trackingvalue
     * @param string $response
     * @return null
     */
    protected function _parseXmlTrackingResponse($trackingvalue, $xmlResponse)
    {
        $errorTitle = 'Unable to retrieve tracking';
        $resultArr = array();
        $packageProgress = array();

        if ($xmlResponse) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//TrackResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0][0];

            if($success===1){
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
                    $i=1;
                    foreach ($activityTags as $activityTag) {
                        $addArr=array();
                        if (isset($activityTag->ActivityLocation->Address->City)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->City;
                        }
                        if (isset($activityTag->ActivityLocation->Address->StateProvinceCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->StateProvinceCode;
                        }
                        if (isset($activityTag->ActivityLocation->Address->CountryCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->CountryCode;
                        }
                        $dateArr = array();
                        $date = (string)$activityTag->Date;//YYYYMMDD
                        $dateArr[] = substr($date,0,4);
                        $dateArr[] = substr($date,4,2);
                        $dateArr[] = substr($date,-2,2);

                        $timeArr = array();
                        $time = (string)$activityTag->Time;//HHMMSS
                        $timeArr[] = substr($time,0,2);
                        $timeArr[] = substr($time,2,2);
                        $timeArr[] = substr($time,-2,2);

                        if($i==1){
                           $resultArr['status'] = (string)$activityTag->Status->StatusType->Description;
                           $resultArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $resultArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           $resultArr['deliverylocation'] = (string)$activityTag->ActivityLocation->Description;
                           $resultArr['signedby'] = (string)$activityTag->ActivityLocation->SignedForByName;
                           if ($addArr) {
                            $resultArr['deliveryto']=implode(', ',$addArr);
                           }
                        }else{
                           $tempArr=array();
                           $tempArr['activity'] = (string)$activityTag->Status->StatusType->Description;
                           $tempArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $tempArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           if ($addArr) {
                            $tempArr['deliverylocation']=implode(', ',$addArr);
                           }
                           $packageProgress[] = $tempArr;
                        }
                        $i++;
                    }
                    $resultArr['progressdetail'] = $packageProgress;
                }
            } else {
                $arr = $xml->getXpath("//TrackResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
            }
        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('Mage_Shipping_Model_Tracking_Result');
        }

        $defaults = $this->getDefaults();

        if ($resultArr) {
            $tracking = Mage::getModel('Mage_Shipping_Model_Tracking_Result_Status');
            $tracking->setCarrier('ups');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            $tracking->addData($resultArr);
            $this->_result->append($tracking);
        } else {
            $error = Mage::getModel('Mage_Shipping_Model_Tracking_Result_Error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
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
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking){
                    if($data = $tracking->getAllData()){
                        if (isset($data['status'])) {
                            $statuses .= Mage::helper('Mage_Usa_Helper_Data')->__($data['status']);
                        } else {
                            $statuses .= Mage::helper('Mage_Usa_Helper_Data')->__($data['error_message']);
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('Mage_Usa_Helper_Data')->__('Empty response');
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
         $isByCode = $this->getConfigData('type') == 'UPS_XML';
         foreach ($allowed as $k) {
             $arr[$k] = $isByCode ? $this->getShipmentByCode($k) : $this->getCode('method', $k);
         }
         return $arr;
    }

    /**
     * Form XML for shipment request
     *
     * @param Varien_Object $request
     * @return string
     */
    protected function _formShipmentRequest(Varien_Object $request)
    {
        $packageParams = $request->getPackageParams();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == Zend_Measure_Weight::POUND ? 'LBS' : 'KGS';
        $dimensionsUnits = $packageParams->getDimensionUnits() == Zend_Measure_Length::INCH ? 'IN' : 'CM';

        $itemsDesc = array();
        $itemsShipment = $request->getPackageItems();
        foreach ($itemsShipment as $itemShipment) {
            $item = new Varien_Object();
            $item->setData($itemShipment);
            $itemsDesc[] = $item->getName();
        }

        $xmlRequest = new SimpleXMLElement('<?xml version = "1.0" ?><ShipmentConfirmRequest xml:lang="en-US"/>');
        $requestPart = $xmlRequest->addChild('Request');
        $requestPart->addChild('RequestAction', 'ShipConfirm');
        $requestPart->addChild('RequestOption', 'nonvalidate');

        $shipmentPart = $xmlRequest->addChild('Shipment');
        if ($request->getIsReturn()) {
            $returnPart = $shipmentPart->addChild('ReturnService');
            // UPS Print Return Label
            $returnPart->addChild('Code', '9');
        }
        $shipmentPart->addChild('Description', substr(implode(' ', $itemsDesc), 0, 35));//empirical

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
        $shipToPart->addChild('CompanyName', $request->getRecipientContactCompanyName()
            ? $request->getRecipientContactCompanyName()
            : 'N/A');
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
            $shipFromPart->addChild('CompanyName', $request->getShipperContactCompanyName()
                ? $request->getShipperContactCompanyName()
                : $request->getShipperContactPersonName());
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
        $packagePart->addChild('Description', substr(implode(' ', $itemsDesc), 0, 35));//empirical
        $packagePart->addChild('PackagingType')
            ->addChild('Code', $request->getPackagingType());
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
        if ($this->_isUSCountry($request->getRecipientAddressCountryCode())
            && $this->_isUSCountry($request->getShipperAddressCountryCode())
        ) {
            if ($request->getReferenceData()) {
                $referenceData = $request->getReferenceData() . $request->getPackageId();
            } else {
                $referenceData = 'Order #'
                                 . $request->getOrderShipment()->getOrder()->getIncrementId()
                                 . ' P'
                                 . $request->getPackageId();
            }
            $referencePart = $packagePart->addChild('ReferenceNumber');
            $referencePart->addChild('Code', '02');
            $referencePart->addChild('Value', $referenceData);
        }

        $deliveryConfirmation = $packageParams->getDeliveryConfirmation();
        if ($deliveryConfirmation) {
            /** @var $serviceOptionsNode SimpleXMLElement */
            $serviceOptionsNode = null;
            switch ($this->_getDeliveryConfirmationLevel($request->getRecipientAddressCountryCode())) {
                case self::DELIVERY_CONFIRMATION_PACKAGE:
                    $serviceOptionsNode = $packagePart->addChild('PackageServiceOptions');
                    break;
                case self::DELIVERY_CONFIRMATION_SHIPMENT:
                    $serviceOptionsNode = $shipmentPart->addChild('ShipmentServiceOptions');
                    break;
            }
            if (!is_null($serviceOptionsNode)) {
                $serviceOptionsNode
                    ->addChild('DeliveryConfirmation')
                    ->addChild('DCISType', $packageParams->getDeliveryConfirmation());
            }
        }

        $shipmentPart->addChild('PaymentInformation')
            ->addChild('Prepaid')
            ->addChild('BillShipper')
            ->addChild('AccountNumber', $this->getConfigData('shipper_number'));

        if ($request->getPackagingType() != $this->getCode('container', 'ULE')
            && $request->getShipperAddressCountryCode() == Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID
            && ($request->getRecipientAddressCountryCode() == 'CA' //Canada
                || $request->getRecipientAddressCountryCode() == 'PR' //Puerto Rico
        )) {
            $invoiceLineTotalPart = $shipmentPart->addChild('InvoiceLineTotal');
            $invoiceLineTotalPart->addChild('CurrencyCode', $request->getBaseCurrencyCode());
            $invoiceLineTotalPart->addChild('MonetaryValue', ceil($packageParams->getCustomsValue()));
        }

        $labelPart = $xmlRequest->addChild('LabelSpecification');
        $labelPart->addChild('LabelPrintMethod')
                ->addChild('Code', 'GIF');
        $labelPart->addChild('LabelImageFormat')
                ->addChild('Code', 'GIF');

        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest . $xmlRequest->asXml();
        return $xmlRequest;
    }

    /**
     * Send and process shipment accept request
     *
     * @param SimpleXMLElement
     * @return Varien_Object
     */
    protected function _sendShipmentAcceptRequest(SimpleXMLElement $shipmentConfirmResponse)
    {
        $xmlRequest = new SimpleXMLElement('<?xml version = "1.0" ?><ShipmentAcceptRequest/>');
        $request = $xmlRequest->addChild('Request');
            $request->addChild('RequestAction', 'ShipAccept');
        $xmlRequest->addChild('ShipmentDigest', $shipmentConfirmResponse->ShipmentDigest);

        $debugData = array('request' => $xmlRequest->asXML());
        try {
            $url = $this->_defaultUrls['ShipAccept'];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_xmlAccessRequest . $xmlRequest->asXML());
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
            $xmlResponse = curl_exec ($ch);

            $debugData['result'] = $xmlResponse;
            $this->_setCachedQuotes($xmlRequest, $xmlResponse);
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }

        try {
            $response = new SimpleXMLElement($xmlResponse);
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
        }

        $result = new Varien_Object();
        if (isset($response->Error)) {
            $result->setErrors((string)$response->Error->ErrorDescription);
        } else {
            $shippingLabelContent = (string)$response->ShipmentResults->PackageResults->LabelImage->GraphicImage;
            $trackingNumber       = (string)$response->ShipmentResults->PackageResults->TrackingNumber;

            $result->setShippingLabelContent(base64_decode($shippingLabelContent));
            $result->setTrackingNumber($trackingNumber);
        }

        $this->_debug($debugData);
        return $result;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param Varien_Object $request
     * @return Varien_Object
     */
    protected function _doShipmentRequest(Varien_Object $request)
    {
        $this->_prepareShipmentRequest($request);
        $result = new Varien_Object();
        $xmlRequest = $this->_formShipmentRequest($request);
        $xmlResponse = $this->_getCachedQuotes($xmlRequest);

        if ($xmlResponse === null) {
            $url = $this->getConfigData('url');
            if (!$url) {
                $url = $this->_defaultUrls['ShipConfirm'];
            }

            $debugData = array('request' => $xmlRequest);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
            $xmlResponse = curl_exec($ch);
            if ($xmlResponse === false) {
                throw new Exception(curl_error($ch));
            } else {
                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            }
        }

        try {
            $response = new SimpleXMLElement($xmlResponse);
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $result->setErrors($e->getMessage());
        }

        if (isset($response->Response->Error)
            && in_array($response->Response->Error->ErrorSeverity, array('Hard', 'Transient'))
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
     * Return container types of carrier
     *
     * @param Varien_Object|null $params
     * @return array|bool
     */
    public function getContainerTypes(Varien_Object $params = null)
    {
        if ($params == null) {
            return $this->_getAllowedContainers($params);
        }
        $method             = $params->getMethod();
        $countryShipper     = $params->getCountryShipper();
        $countryRecipient   = $params->getCountryRecipient();

        if (($countryShipper == self::USA_COUNTRY_ID
            && $countryRecipient == self::CANADA_COUNTRY_ID)
            || ($countryShipper == self::CANADA_COUNTRY_ID
            && $countryRecipient == self::USA_COUNTRY_ID)
            || ($countryShipper == self::MEXICO_COUNTRY_ID
            && $countryRecipient == self::USA_COUNTRY_ID)
            && $method == '11' // UPS Standard
        ) {
            $containerTypes = array();
            if ($method == '07' // Worldwide Express
                || $method == '08' // Worldwide Expedited
                || $method == '65' // Worldwide Saver

            ) {
                // Worldwide Expedited
                if ($method != '08') {
                    $containerTypes = array(
                        '01'   => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Letter Envelope'),
                        '24'   => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide 25 kilo'),
                        '25'   => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Worldwide 10 kilo'),
                    );
                }
                $containerTypes = $containerTypes + array(
                    '03'     => Mage::helper('Mage_Usa_Helper_Data')->__('UPS Tube'),
                    '04'    => Mage::helper('Mage_Usa_Helper_Data')->__('PAK'),
                    '2a'    => Mage::helper('Mage_Usa_Helper_Data')->__('Small Express Box'),
                    '2b'    => Mage::helper('Mage_Usa_Helper_Data')->__('Medium Express Box'),
                    '2c'    => Mage::helper('Mage_Usa_Helper_Data')->__('Large Express Box'),
                );
            }
            return array('00' => Mage::helper('Mage_Usa_Helper_Data')->__('Customer Packaging')) + $containerTypes;
        } elseif ($countryShipper == self::USA_COUNTRY_ID && $countryRecipient == self::PUERTORICO_COUNTRY_ID
            && ($method == '03' // UPS Ground
            || $method == '02' // UPS Second Day Air
            || $method == '01' // UPS Next Day Air
        )) {
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
        $codes        = $this->getCode('container');
        $descriptions = $this->getCode('container_description');
        $result       = array();
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
        return $this->getCode('containers_filter');
    }

    /**
     * Return delivery confirmation types of carrier
     *
     * @param Varien_Object|null $params
     * @return array|bool
     */
    public function getDeliveryConfirmationTypes(Varien_Object $params = null)
    {
        $countryRecipient           = $params != null ? $params->getCountryRecipient() : null;
        $deliveryConfirmationTypes  = array();
        switch ($this->_getDeliveryConfirmationLevel($countryRecipient)) {
            case self::DELIVERY_CONFIRMATION_PACKAGE:
                $deliveryConfirmationTypes = array(
                    1 => Mage::helper('Mage_Usa_Helper_Data')->__('Delivery Confirmation'),
                    2 => Mage::helper('Mage_Usa_Helper_Data')->__('Signature Required'),
                    3 => Mage::helper('Mage_Usa_Helper_Data')->__('Adult Signature Required'),
                );
                break;
            case self::DELIVERY_CONFIRMATION_SHIPMENT:
                $deliveryConfirmationTypes = array(
                    1 => Mage::helper('Mage_Usa_Helper_Data')->__('Signature Required'),
                    2 => Mage::helper('Mage_Usa_Helper_Data')->__('Adult Signature Required'),
                );
        }
        array_unshift($deliveryConfirmationTypes, Mage::helper('Mage_Usa_Helper_Data')->__('Not Required'));

        return $deliveryConfirmationTypes;
    }

    /**
     * Get Container Types, that could be customized for UPS carrier
     *
     * @return array
     */
    public function getCustomizableContainerTypes()
    {
        $result = array();
        $containerTypes = $this->getCode('container');
        foreach (parent::getCustomizableContainerTypes() as $containerType) {
            $result[$containerType] = $containerTypes[$containerType];
        }
        return $result;
    }

    /**
     * Get delivery confirmation level based on origin/destination
     * Return null if delivery confirmation is not acceptable
     *
     * @var string $countyDest
     * @return int|null
     */
    protected function _getDeliveryConfirmationLevel($countyDest = null) {
        if (is_null($countyDest)) {
            return null;
        }

        if ($countyDest == Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID) {
            return self::DELIVERY_CONFIRMATION_PACKAGE;
        }

        return self::DELIVERY_CONFIRMATION_SHIPMENT;
    }
}
