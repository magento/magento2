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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Shipping_Model_Shipping
{
    /**
     * Store address
     */
    const XML_PATH_STORE_ADDRESS1     = 'shipping/origin/street_line1';
    const XML_PATH_STORE_ADDRESS2     = 'shipping/origin/street_line2';
    const XML_PATH_STORE_CITY         = 'shipping/origin/city';
    const XML_PATH_STORE_REGION_ID    = 'shipping/origin/region_id';
    const XML_PATH_STORE_ZIP          = 'shipping/origin/postcode';
    const XML_PATH_STORE_COUNTRY_ID   = 'shipping/origin/country_id';

    /**
     * Default shipping orig for requests
     *
     * @var array
     */
    protected $_orig = null;

    /**
     * Cached result
     *
     * @var Mage_Sales_Model_Shipping_Method_Result
     */
    protected $_result = null;

    /**
     * Part of carrier xml config path
     *
     * @var string
     */
    protected $_availabilityConfigField = 'active';

    /**
     * Get shipping rate result model
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function getResult()
    {
        if (empty($this->_result)) {
            $this->_result = Mage::getModel('Mage_Shipping_Model_Rate_Result');
        }
        return $this->_result;
    }

    /**
     * Set shipping orig data
     *
     * @param array $data
     * @return null
     */
    public function setOrigData($data)
    {
        $this->_orig = $data;
    }

    /**
     * Reset cached result
     *
     * @return Mage_Shipping_Model_Shipping
     */
    public function resetResult()
    {
        $this->getResult()->reset();
        return $this;
    }

    /**
     * Retrieve configuration model
     *
     * @return Mage_Shipping_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('Mage_Shipping_Model_Config');
    }

    /**
     * Retrieve all methods for supplied shipping data
     *
     * @todo make it ordered
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $storeId = $request->getStoreId();
        if (!$request->getOrig()) {
            $request
                ->setCountryId(Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $request->getStore()))
                ->setRegionId(Mage::getStoreConfig(self::XML_PATH_STORE_REGION_ID, $request->getStore()))
                ->setCity(Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $request->getStore()))
                ->setPostcode(Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $request->getStore()));
        }

        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = Mage::getStoreConfig('carriers', $storeId);

            foreach ($carriers as $carrierCode => $carrierConfig) {
                $this->collectCarrierRates($carrierCode, $request);
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = Mage::getStoreConfig('carriers/' . $carrierCode, $storeId);
                if (!$carrierConfig) {
                    continue;
                }
                $this->collectCarrierRates($carrierCode, $request);
            }
        }

        return $this;
    }

    /**
     * Collect rates of given carrier
     *
     * @param string                           $carrierCode
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectCarrierRates($carrierCode, $request)
    {
        /* @var $carrier Mage_Shipping_Model_Carrier_Abstract */
        $carrier = $this->getCarrierByCode($carrierCode, $request->getStoreId());
        if (!$carrier) {
            return $this;
        }
        $carrier->setActiveFlag($this->_availabilityConfigField);
        $result = $carrier->checkAvailableShipCountries($request);
        if (false !== $result && !($result instanceof Mage_Shipping_Model_Rate_Result_Error)) {
            $result = $carrier->processAdditionalValidation($request);
        }
        /*
        * Result will be false if the admin set not to show the shipping module
        * if the delivery country is not within specific countries
        */
        if (false !== $result){
            if (!$result instanceof Mage_Shipping_Model_Rate_Result_Error) {
                $result = $carrier->collectRates($request);
                if (!$result) {
                    return $this;
                }
            }
            if ($carrier->getConfigData('showmethod') == 0 && $result->getError()) {
                return $this;
            }
            // sort rates by price
            if (method_exists($result, 'sortRatesByPrice')) {
                $result->sortRatesByPrice();
            }
            $this->getResult()->append($result);
        }
        return $this;
    }

    /**
     * Collect rates by address
     *
     * @param Varien_Object $address
     * @param null|bool|array $limitCarrier
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRatesByAddress(Varien_Object $address, $limitCarrier = null)
    {
        /** @var $request Mage_Shipping_Model_Rate_Request */
        $request = Mage::getModel('Mage_Shipping_Model_Rate_Request');
        $request->setAllItems($address->getAllItems());
        $request->setDestCountryId($address->getCountryId());
        $request->setDestRegionId($address->getRegionId());
        $request->setDestPostcode($address->getPostcode());
        $request->setPackageValue($address->getBaseSubtotal());
        $request->setPackageValueWithDiscount($address->getBaseSubtotalWithDiscount());
        $request->setPackageWeight($address->getWeight());
        $request->setFreeMethodWeight($address->getFreeMethodWeight());
        $request->setPackageQty($address->getItemQty());
        $request->setStoreId(Mage::app()->getStore()->getId());
        $request->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $request->setBaseCurrency(Mage::app()->getStore()->getBaseCurrency());
        $request->setPackageCurrency(Mage::app()->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($limitCarrier);

        return $this->collectRates($request);
    }

    /**
     * Set part of carrier xml config path
     *
     * @param string $code
     * @return Mage_Shipping_Model_Shipping
     */
    public function setCarrierAvailabilityConfigField($code = 'active')
    {
        $this->_availabilityConfigField = $code;
        return $this;
    }

    /**
     * Get carrier by its code
     *
     * @param string $carrierCode
     * @param null|int $storeId
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getCarrierByCode($carrierCode, $storeId = null)
    {
        if (!Mage::getStoreConfigFlag('carriers/'.$carrierCode.'/'.$this->_availabilityConfigField, $storeId)) {
            return false;
        }
        $className = Mage::getStoreConfig('carriers/'.$carrierCode.'/model', $storeId);
        if (!$className) {
            return false;
        }
        $obj = Mage::getModel($className);
        if ($storeId) {
            $obj->setStore($storeId);
        }
        return $obj;
    }

    /**
     * Prepare and do request to shipment
     *
     * @param Mage_Sales_Model_Order_Shipment $orderShipment
     * @return Varien_Object
     */
    public function requestToShipment(Mage_Sales_Model_Order_Shipment $orderShipment)
    {
        $admin = Mage::getSingleton('Mage_Admin_Model_Session')->getUser();
        $order = $orderShipment->getOrder();
        $address = $order->getShippingAddress();
        $shippingMethod = $order->getShippingMethod(true);
        $shipmentStoreId = $orderShipment->getStoreId();
        $shipmentCarrier = $order->getShippingCarrier();
        $baseCurrencyCode = Mage::app()->getStore($shipmentStoreId)->getBaseCurrencyCode();
        if (!$shipmentCarrier) {
            Mage::throwException('Invalid carrier: ' . $shippingMethod->getCarrierCode());
        }
        $shipperRegionCode = Mage::getStoreConfig(self::XML_PATH_STORE_REGION_ID, $shipmentStoreId);
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = Mage::getModel('Mage_Directory_Model_Region')->load($shipperRegionCode)->getCode();
        }

        $recipientRegionCode = Mage::getModel('Mage_Directory_Model_Region')->load($address->getRegionId())->getCode();

        $originStreet1 = Mage::getStoreConfig(self::XML_PATH_STORE_ADDRESS1, $shipmentStoreId);
        $originStreet2 = Mage::getStoreConfig(self::XML_PATH_STORE_ADDRESS2, $shipmentStoreId);
        $storeInfo = new Varien_Object(Mage::getStoreConfig('general/store_information', $shipmentStoreId));

        if (!$admin->getFirstname() || !$admin->getLastname() || !$storeInfo->getName() || !$storeInfo->getPhone()
            || !$originStreet1 || !Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $shipmentStoreId)
            || !$shipperRegionCode || !Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $shipmentStoreId)
            || !Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId)
        ) {
            Mage::throwException(
                Mage::helper('Mage_Sales_Helper_Data')->__('Insufficient information to create shipping label(s). Please verify your Store Information and Shipping Settings.')
            );
        }

        /** @var $request Mage_Shipping_Model_Shipment_Request */
        $request = Mage::getModel('Mage_Shipping_Model_Shipment_Request');
        $request->setOrderShipment($orderShipment);
        $request->setShipperContactPersonName($admin->getName());
        $request->setShipperContactPersonFirstName($admin->getFirstname());
        $request->setShipperContactPersonLastName($admin->getLastname());
        $request->setShipperContactCompanyName($storeInfo->getName());
        $request->setShipperContactPhoneNumber($storeInfo->getPhone());
        $request->setShipperEmail($admin->getEmail());
        $request->setShipperAddressStreet(trim($originStreet1 . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet1);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity(Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $shipmentStoreId));
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode(Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $shipmentStoreId));
        $request->setShipperAddressCountryCode(Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId));
        $request->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim($address->getStreet1() . ' ' . $address->getStreet2()));
        $request->setRecipientAddressStreet1($address->getStreet1());
        $request->setRecipientAddressStreet2($address->getStreet2());
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegion());
        $request->setRecipientAddressRegionCode($recipientRegionCode);
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());
        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);

        return $shipmentCarrier->requestToShipment($request);
    }
}
