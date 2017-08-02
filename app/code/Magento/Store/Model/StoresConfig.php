<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class \Magento\Store\Model\StoresConfig
 *
 * @since 2.0.0
 */
class StoresConfig
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->_storeManager = $storeManager;
        $this->_config = $config;
    }

    /**
     * Retrieve store Ids for $path with checking
     *
     * return array($storeId => $pathValue)
     *
     * @param string $path
     * @return array
     * @since 2.0.0
     */
    public function getStoresConfigByPath($path)
    {
        $stores = $this->_storeManager->getStores(true);
        $storeValues = [];
        /** @var $store \Magento\Store\Model\Store */
        foreach ($stores as $store) {
            try {
                $value = $this->_config->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode());
                $storeValues[$store->getId()] = $value;
            } catch (NoSuchEntityException $e) {
                // Store doesn't really exist, so move on.
                continue;
            }
        }
        return $storeValues;
    }
}
