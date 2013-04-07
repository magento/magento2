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
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Shipping_Model_Carrier_ServiceAdapter extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    const CYCLE_DETECTED_MARK = '*** Cycle Detected ***';
    /** @var string */
    protected $_serviceClassName;

    /** @var Magento_ObjectManager */
    protected $_serviceFactory;

    /** @var Mage_Core_Model_Logger */
    protected $_logger;

    /** @var Mage_Shipping_Model_Carrier_Service_Result */
    protected $_serviceResult;

    /**
     * @param Mage_Core_Model_Logger $logger
     * @param $serviceClassName
     * @param Magento_ObjectManager $serviceFactory
     */
    public function __construct(
        Mage_Core_Model_Logger $logger,
        $serviceClassName,
        Magento_ObjectManager $objectManager)
    {
        $this->_logger = $logger;
        $this->_serviceClassName = $serviceClassName;
        $this->_serviceFactory = $objectManager;
        $this->_serviceResult = new Mage_Shipping_Model_Carrier_Service_Result();
    }

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        try {
            $serviceInput = $this->_constructInput($request);
            $serviceOutput = $this->_getService()->getRates($serviceInput);
            return $this->_createRateResult($serviceOutput);
        } catch (Exception $ex) {
            $this->_logger->logException($ex);
            return $this->_createErrorRateResult($ex);
        }
    }

    /**
     * Construct array of data to be sent to web service based on input rate request.
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return array
     */
    protected function _constructInput($request)
    {
        $inputFields = array();
        $inputFields['carrierConfiguration'] = $this->_getCarrierMetadata()->getCarrierConfig();
        $inputFields['rateRequest'] = $this->_convertObjectToArray($request);
        // packageValue
        if (isset($inputFields['rateRequest']['packageValue'])) {
            $pkgPhysicalVal = $inputFields['rateRequest']['packageValue'];
            unset($inputFields['rateRequest']['packageValue']);
            $inputFields['rateRequest']['packageValue']['amount'] = $pkgPhysicalVal;
            $inputFields['rateRequest']['packageValue']['currencyCode'] = $inputFields['rateRequest']['packageCurrency']['currencyCode'];
        }

        // packageValueWithDiscount
        if (isset($inputFields['rateRequest']['packageValueWithDiscount'])) {
            $pkgPhysicalVal = $inputFields['rateRequest']['packageValueWithDiscount'];
            unset($inputFields['rateRequest']['packageValueWithDiscount']);
            $inputFields['rateRequest']['packageValueWithDiscount']['amount'] = $pkgPhysicalVal;
            $inputFields['rateRequest']['packageValueWithDiscount']['currencyCode'] = $inputFields['rateRequest']['packageCurrency']['currencyCode'];
        }

        // packagePhysicalValue
        if (isset($inputFields['rateRequest']['packagePhysicalValue'])) {
            $pkgPhysicalVal = $inputFields['rateRequest']['packagePhysicalValue'];
            unset($inputFields['rateRequest']['packagePhysicalValue']);
            $inputFields['rateRequest']['packagePhysicalValue']['amount'] = $pkgPhysicalVal;
            $inputFields['rateRequest']['packagePhysicalValue']['currencyCode'] = $inputFields['rateRequest']['packageCurrency']['currencyCode'];
        }

        //baseSubtotalWithTax
        if (isset($inputFields['rateRequest']['baseSubtotalInclTax'])) {
            $inputFields['rateRequest']['baseSubtotalWithTax']['amount'] = $inputFields['rateRequest']['baseSubtotalInclTax'];
            $inputFields['rateRequest']['baseSubtotalWithTax']['currencyCode'] = $inputFields['rateRequest']['packageCurrency'];
            unset($inputFields['rateRequest']['baseSubtotalInclTax']);
        }

        // destination
        if (isset($inputFields['rateRequest']['destCountryId'])) {
            $destCountry = $request->getDestCountryId();
            unset($inputFields['rateRequest']['destCountryId']);
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $inputFields['rateRequest']['destination']['countryId'] = Mage::getModel('Mage_Directory_Model_Country')->load($destCountry)->getIso2Code();

        if (isset($inputFields['rateRequest']['destRegionCode'])) {
            $inputFields['rateRequest']['destination']['region'] = $inputFields['rateRequest']['destRegionCode'];
            unset($inputFields['rateRequest']['destRegionCode']);
        }

        if (isset($inputFields['rateRequest']['destPostcode'])) {
            $inputFields['rateRequest']['destination']['postcode'] = $inputFields['rateRequest']['destPostcode'];
            unset($inputFields['rateRequest']['destPostcode']);
        }

        if (isset($inputFields['rateRequest']['destStreet'])) {
            $inputFields['rateRequest']['destination']['street'] = $inputFields['rateRequest']['destStreet'];
            unset($inputFields['rateRequest']['destStreet']);
        }

        if (isset($inputFields['rateRequest']['destCity'])) {
            $inputFields['rateRequest']['destination']['city'] = $inputFields['rateRequest']['destCity'];
            unset($inputFields['rateRequest']['destCity']);
        }

        // Origin
        if (isset($inputFields['rateRequest']['countryId'])) {
            $origCountry = $inputFields['rateRequest']['countryId'];
            unset($inputFields['rateRequest']['countryId']);
        } else {
            $origCountry = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                $inputFields['rateRequest']['storeId']
            );
        }
        $inputFields['rateRequest']['origin']['countryId'] = Mage::getModel('Mage_Directory_Model_Country')->load($origCountry)->getIso2Code();

        if (isset($inputFields['rateRequest']['regionId'])) {
            $regionCode = $inputFields['rateRequest']['regionId'];
            unset($inputFields['rateRequest']['regionId']);
        } else {
            $regionCode = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID,
                $inputFields['rateRequest']['storeId']
            );
        }
        if (is_numeric($regionCode)) {
            $inputFields['rateRequest']['origin']['region'] = Mage::getModel('Mage_Directory_Model_Region')->load($regionCode)->getCode();
        } else {
            $inputFields['rateRequest']['origin']['region'] = $regionCode;
        }

        if (isset($inputFields['rateRequest']['postcode'])) {
            $inputFields['rateRequest']['origin']['postcode'] = $inputFields['rateRequest']['postcode'];
            unset($inputFields['rateRequest']['postcode']);
        } else {
            $inputFields['rateRequest']['origin']['postcode'] = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP,
                $inputFields['rateRequest']['storeId']
            );
        }

        if (isset($inputFields['rateRequest']['city'])) {
            $inputFields['rateRequest']['origin']['city'] = $inputFields['rateRequest']['city'];
            unset($inputFields['rateRequest']['city']);
        } else {
            $inputFields['rateRequest']['origin']['city'] = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY,
                $inputFields['rateRequest']['storeId']
            );
        }

        if (isset($inputFields['rateRequest']['street'])) {
            $inputFields['rateRequest']['origin']['street'] = $inputFields['rateRequest']['street'];
            unset($inputFields['rateRequest']['street']);
        } else {
            $address1 = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1,
                $inputFields['rateRequest']['storeId']
            );
            $address2 = Mage::getStoreConfig(
                Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2,
                $inputFields['rateRequest']['storeId']
            );
            $inputFields['rateRequest']['origin']['street'] = $address1 . ' ' . $address2;
        }

        //items
        //@TODO need to match xsd
        if (isset($inputFields['rateRequest']['allItems'])) {
            $inputFields['rateRequest']['items'] = $inputFields['rateRequest']['allItems'];
            unset($inputFields['rateRequest']['allItems']);
        }
        return $inputFields;
    }

    protected function _getService()
    {
        return $this->_serviceFactory->create($this->_serviceClassName);
    }

    protected function _getCarrierMetadata()
    {
        $arguments = array('carrierCode' => $this->_code, 'carrierConfig' => $this->_getCarrierConfig());
        return Mage::getModel('Mage_Shipping_Model_Carrier_Metadata', $arguments);
    }

    protected function _getCarrierConfig()
    {
        $path = 'carriers/'.$this->_code;
        $config = Mage::getStoreConfig($path, $this->getStore());
        $configCamelcaseKey = array();
        foreach ($config as $key => $value) {
            $configCamelcaseKey[$this->_to_camel_case($key)] = $value;
        }
        return $configCamelcaseKey;
    }

    /**
     * @param $serviceOutput array
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _createRateResult($serviceOutput)
    {
        return $this->_getServiceResult()->createRateResult($serviceOutput);
    }

    /**
     * @return Mage_Shipping_Model_Carrier_Service_Result
     */
    protected function _getServiceResult()
    {
        return $this->_serviceResult;
    }

    /**
     * @param $ex Exception
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _createErrorRateResult($ex)
    {
        return $this->_getServiceResult()->createErrorRateResult($ex);
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return $this->getConfigFlag('tracking_available');
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = $this->getConfigData('shipping_methods');
        $allowedMethods = array();
        foreach ($methods as $methodCode => $methodName) {
            $allowedMethods[] = array($methodCode => $methodName);
        }
        return $allowedMethods;
    }

    public function setCarrierCode($code)
    {
        $this->_code = $code;
    }


    /**
     * Converts a Varien_Object into an array, including any children objects
     *
     * Code is very similar to what Webhooks considers their Default Mapper
     *
     * @param Varien_Object $obj
     * @param array $objects
     * @param bool $performRedaction If set to true will redact any fields returned from _getListOfRedactedFields.
     * @return array|string
     */
    protected function _convertObjectToArray($obj, &$objects = array())
    {
        if (is_object($obj)) {
            $hash = spl_object_hash($obj);
            if (!empty($objects[$hash])) {
                return self::CYCLE_DETECTED_MARK;
            }
            $objects[$hash] = true;
            $data = $obj->getData();
        }
        else if (is_array($obj)) {
            $data = $obj;
        }

        $result = array();
        foreach ($data as $key=>$value) {
            if (is_scalar($value)) {
                $result[$this->_to_camel_case($key)] = $value;
            } elseif (is_array($value)) {
                $result[$this->_to_camel_case($key)] = $this->_convertObjectToArray($value, $objects);
            } elseif ($value instanceof Varien_Object) {
                $result[$this->_to_camel_case($key)] = $this->_convertObjectToArray($value, $objects);
            }
        }
        return $result;
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
     * @param    string   $str    String in camel case format
     * @return    string            $str Translated into underscore format
     */
    protected function _from_camel_case($str) {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     * @param    string   $str                     String in underscore format
     * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
     * @return   string                              $str translated into camel caps
     */
    protected function _to_camel_case($str, $capitalise_first_char = false) {
        if($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }
}