<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Shipping\Model;

class Config extends \Magento\Framework\DataObject
{
    /**
     * Shipping origin settings
     */
    const XML_PATH_ORIGIN_COUNTRY_ID = 'shipping/origin/country_id';

    const XML_PATH_ORIGIN_REGION_ID = 'shipping/origin/region_id';

    const XML_PATH_ORIGIN_CITY = 'shipping/origin/city';

    const XML_PATH_ORIGIN_POSTCODE = 'shipping/origin/postcode';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
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
        $carriers = [];
        $config = $this->_scopeConfig->getValue('carriers', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        foreach (array_keys($config) as $carrierCode) {
            if ($this->_scopeConfig->isSetFlag('carriers/' . $carrierCode . '/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
                $carrierModel = $this->_carrierFactory->create($carrierCode, $store);
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
        $carriers = [];
        $config = $this->_scopeConfig->getValue('carriers', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        foreach (array_keys($config) as $carrierCode) {
            $model = $this->_carrierFactory->create($carrierCode, $store);
            if ($model) {
                $carriers[$carrierCode] = $model;
            }
        }
        return $carriers;
    }
}
