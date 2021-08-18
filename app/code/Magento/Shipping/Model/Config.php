<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Config model for shipping
 * @api
 * @since 100.0.2
 */
class Config extends DataObject
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
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CarrierFactory $carrierFactory,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_carrierFactory = $carrierFactory;
        parent::__construct($data);
    }

    /**
     * Retrieve active system carriers
     *
     * @param mixed $store
     * @return AbstractCarrierInterface[]
     */
    public function getActiveCarriers($store = null)
    {
        $carriers = [];
        $config = $this->getCarriersConfig($store);
        foreach (array_keys($config) as $carrierCode) {
            if ($this->_scopeConfig->isSetFlag(
                'carriers/' . $carrierCode . '/active',
                ScopeInterface::SCOPE_STORE,
                $store
            )) {
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
     * @param mixed $store
     * @return AbstractCarrierInterface[]
     */
    public function getAllCarriers($store = null)
    {
        $carriers = [];
        $config = $this->getCarriersConfig($store);
        foreach (array_keys($config) as $carrierCode) {
            $model = $this->_carrierFactory->create($carrierCode, $store);
            if ($model) {
                $carriers[$carrierCode] = $model;
            }
        }

        return $carriers;
    }

    /**
     * Returns carriers config by store
     *
     * @param mixed $store
     * @return array
     */
    private function getCarriersConfig($store = null): array
    {
        return $this->_scopeConfig->getValue('carriers', ScopeInterface::SCOPE_STORE, $store) ?: [];
    }
}
