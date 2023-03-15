<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CarrierFactory
 */
class CarrierFactory implements CarrierFactoryInterface
{
    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE,
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
            ScopeInterface::SCOPE_STORE
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
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ? $this->create(
            $carrierCode,
            $storeId
        ) : false;
    }
}
