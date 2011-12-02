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


class Mage_Shipping_Model_Config extends Varien_Object
{
    /**
     * Shipping origin settings
     */
    const XML_PATH_ORIGIN_COUNTRY_ID = 'shipping/origin/country_id';
    const XML_PATH_ORIGIN_REGION_ID  = 'shipping/origin/region_id';
    const XML_PATH_ORIGIN_CITY       = 'shipping/origin/city';
    const XML_PATH_ORIGIN_POSTCODE   = 'shipping/origin/postcode';

    protected static $_carriers;

    /**
     * Retrieve active system carriers
     *
     * @param   mixed $store
     * @return  array
     */
    public function getActiveCarriers($store = null)
    {
        $carriers = array();
        $config = Mage::getStoreConfig('carriers', $store);
        foreach ($config as $code => $carrierConfig) {
            if (Mage::getStoreConfigFlag('carriers/'.$code.'/active', $store)) {
                $carrierModel = $this->_getCarrier($code, $carrierConfig, $store);
                if ($carrierModel) {
                    $carriers[$code] = $carrierModel;
                }
            }
        }
        return $carriers;
    }

    /**
     * Retrieve all system carriers
     *
     * @param   mixed $store
     * @return  array
     */
    public function getAllCarriers($store = null)
    {
        $carriers = array();
        $config = Mage::getStoreConfig('carriers', $store);
        foreach ($config as $code => $carrierConfig) {
            $model = $this->_getCarrier($code, $carrierConfig, $store);
            if ($model) {
                $carriers[$code] = $model;
            }
        }
        return $carriers;
    }

    /**
     * Retrieve carrier model instance by carrier code
     *
     * @param   string $carrierCode
     * @param   mixed $store
     * @return  Mage_Usa_Model_Shipping_Carrier_Abstract
     */
    public function getCarrierInstance($carrierCode, $store = null)
    {
        $carrierConfig =  Mage::getStoreConfig('carriers/'.$carrierCode, $store);
        if (!empty($carrierConfig)) {
            return $this->_getCarrier($carrierCode, $carrierConfig, $store);
        }
        return false;
    }

    /**
     * Get carrier model object
     *
     * @param string $code
     * @param array $config
     * @param mixed $store
     * @return Mage_Shipping_Model_Carrier_Abstract
     */
    protected function _getCarrier($code, $config, $store = null)
    {
        if (!isset($config['model'])) {
            return false;
        }
        $modelName = $config['model'];

        /**
         * Added protection from not existing models usage.
         * Related with module uninstall process
         */
        try {
            $carrier = Mage::getModel($modelName);
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        $carrier->setId($code)->setStore($store);
        self::$_carriers[$code] = $carrier;
        return self::$_carriers[$code];
    }
}
