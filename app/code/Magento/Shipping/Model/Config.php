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
 * @package     Magento_Shipping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Shipping\Model;

class Config extends \Magento\Object
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
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Shipping\Model\Carrier\Factory
     */
    protected $_carrierFactory;

    /**
     * Constructor
     *
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Shipping\Model\Carrier\Factory $carrierFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Shipping\Model\Carrier\Factory $carrierFactory,
        array $data = array()
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($data);
    }

    /**
     * Retrieve active system carriers
     *
     * @param   mixed $store
     * @return  array
     */
    public function getActiveCarriers($store = null)
    {
        $carriers = array();
        $config = $this->_coreStoreConfig->getConfig('carriers', $store);
        foreach (array_keys($config) as $carrierCode) {
            if ($this->_coreStoreConfig->getConfigFlag('carriers/' . $carrierCode . '/active', $store)) {
                $carrierModel = $this->_getCarrier($carrierCode, $store);
                if ($carrierModel) {
                    $carriers[$carrierCode] = $carrierModel;
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
        $config = $this->_coreStoreConfig->getConfig('carriers', $store);
        foreach (array_keys($config) as $carrierCode) {
            $model = $this->_getCarrier($carrierCode, $store);
            if ($model) {
                $carriers[$carrierCode] = $model;
            }
        }
        return $carriers;
    }

    /**
     * Retrieve carrier model instance by carrier code
     *
     * @param   string $carrierCode
     * @param   mixed $store
     * @return  \Magento\Usa\Model\Shipping\Carrier\AbstractCarrier
     */
    public function getCarrierInstance($carrierCode, $store = null)
    {
        return $this->_getCarrier($carrierCode, $store);
    }

    /**
     * Get carrier model object
     *
     * @param $carrierCode
     * @param mixed $store
     * @return \Magento\Shipping\Model\Carrier\AbstractCarrier
     */
    protected function _getCarrier($carrierCode, $store = null)
    {
        $carrier = $this->_carrierFactory->create($carrierCode, $store);
        self::$_carriers[$carrierCode] = $carrier;
        return self::$_carriers[$carrierCode];
    }
}
