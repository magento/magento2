<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

/**
 * Class CarrierFactory
 */
class CarrierFactory implements CarrierFactoryInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
    }

    /**
     * Get carrier instance
     *
     * @param string $carrierCode
     * @return bool|Carrier\AbstractCarrier
     */
    public function get($carrierCode)
    {
        $className = $this->_scopeConfig->getValue(
            'carriers/' . $carrierCode . '/model',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$className) {
            return false;
        }
        $carrier = $this->_objectManager->get($className);
        $carrier->setId($carrierCode);
        return $carrier;
    }

    /**
     * Create carrier instance
     *
     * @param string $carrierCode
     * @param int|null $storeId
     * @return bool|Carrier\AbstractCarrier
     */
    public function create($carrierCode, $storeId = null)
    {
        $className = $this->_scopeConfig->getValue(
            'carriers/' . $carrierCode . '/model',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$className) {
            return false;
        }
        $carrier = $this->_objectManager->create($className);
        $carrier->setId($carrierCode);
        if ($storeId) {
            $carrier->setStore($storeId);
        }
        return $carrier;
    }

    /**
     * Get carrier by its code if it is active
     *
     * @param string $carrierCode
     * @return bool|Carrier\AbstractCarrier
     */
    public function getIfActive($carrierCode)
    {
        return $this->_scopeConfig->isSetFlag(
            'carriers/' . $carrierCode . '/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ? $this->get(
            $carrierCode
        ) : false;
    }

    /**
     * Create carrier by its code if it is active
     *
     * @param string $carrierCode
     * @param null|int $storeId
     * @return bool|Carrier\AbstractCarrier
     */
    public function createIfActive($carrierCode, $storeId = null)
    {
        return $this->_scopeConfig->isSetFlag(
            'carriers/' . $carrierCode . '/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ? $this->create(
            $carrierCode,
            $storeId
        ) : false;
    }
}
