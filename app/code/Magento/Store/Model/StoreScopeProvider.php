<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\Model\Entity\ScopeInterface as EntityScopeInterface;
use Magento\Framework\Model\Entity\ScopeProviderInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Model\Entity\ScopeFactory;

/**
 * Class StoreScope
 */
class StoreScopeProvider implements ScopeProviderInterface
{
    /**
     * StoreScopeProvider constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeFactory $scopeFactory
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeFactory $scopeFactory
    ) {
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @return EntityScopeInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContext($entityType, $entityData = [])
    {
        if (isset($entityData[Store::STORE_ID])) {
            $value = $entityData[Store::STORE_ID];
        } else {
            $value = (int)$this->storeManager->getStore(true)->getId();
        }

        $identifier = Store::STORE_ID;
        $fallback = null;
        if ($value != Store::DEFAULT_STORE_ID) {
            $fallback = $this->scopeFactory->create($identifier, Store::DEFAULT_STORE_ID);
        }
        return $this->scopeFactory->create($identifier, $value, $fallback);
    }
}
