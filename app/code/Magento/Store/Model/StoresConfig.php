<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class StoresConfig
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config
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
     */
    public function getStoresConfigByPath($path)
    {
        $stores = $this->_storeManager->getStores(true);
        $storeValues = [];
        /** @var Store $store */
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
