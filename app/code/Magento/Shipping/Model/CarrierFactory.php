<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param int $storeId
     * @param bool $isReturn
     * @return bool|Carrier\AbstractCarrier
     */
    public function getIfActive(string $carrierCode, int $storeId, bool $isReturn)
    {
        return $this->isFlagSet($carrierCode, $storeId, $isReturn) ? $this->get($carrierCode) : false;
    }

    /**
     * Create carrier by its code if it is active
     *
     * @param string $carrierCode
     * @param null|int $storeId
     * @param bool $isReturn
     * @return bool|Carrier\AbstractCarrier
     */
    public function createIfActive(string $carrierCode, int $storeId, bool $isReturn)
    {
        return $this->isFlagSet($carrierCode, $storeId, $isReturn) ? $this->create($carrierCode, $storeId) : false;
    }

    private function isFlagSet(string $carrierCode, int $storeId, bool $isReturn): bool
    {
        return $this->_scopeConfig->isSetFlag(
            $this->getActiveFlagPath($carrierCode, $isReturn),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    private function getActiveFlagPath(string $carrierCode, bool $isReturn): string
    {
        if ($isReturn) {
            return 'carriers/' . $carrierCode . '/active_rma';
        }

        return 'carriers/' . $carrierCode . '/active';
    }
}
