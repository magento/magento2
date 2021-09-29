<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;
use Magento\Shipping\Model\Config\Carriers as CarriersConfig;
use Magento\Shipping\Model\Config\CarrierStatus;

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
    public const XML_PATH_ORIGIN_COUNTRY_ID = 'shipping/origin/country_id';

    public const XML_PATH_ORIGIN_REGION_ID = 'shipping/origin/region_id';

    public const XML_PATH_ORIGIN_CITY = 'shipping/origin/city';

    public const XML_PATH_ORIGIN_POSTCODE = 'shipping/origin/postcode';

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
     * @var CarriersConfig
     */
    private $carriersConfig;

    /**
     * @var CarrierStatus
     */
    private $carrierStatus;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CarrierFactory $carrierFactory,
        array $data = [],
        CarriersConfig $carriersConfig = null,
        CarrierStatus $carrierStatus = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_carrierFactory = $carrierFactory;
        $this->carriersConfig = $carriersConfig ?? ObjectManager::getInstance()->get(CarriersConfig::class);
        $this->carrierStatus = $carrierStatus ?? ObjectManager::getInstance()->get(CarrierStatus::class);
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
        $config = $this->carriersConfig->getConfig($store);
        foreach (array_keys($config) as $carrierCode) {
            if ($this->carrierStatus->isEnabled($carrierCode, $store)) {
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
        $config = $this->carriersConfig->getConfig($store);
        foreach (array_keys($config) as $carrierCode) {
            $model = $this->_carrierFactory->create($carrierCode, $store);
            if ($model) {
                $carriers[$carrierCode] = $model;
            }
        }

        return $carriers;
    }
}
