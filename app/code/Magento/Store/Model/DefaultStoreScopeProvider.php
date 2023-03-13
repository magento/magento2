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
class DefaultStoreScopeProvider implements ScopeProviderInterface
{
    /**
     * StoreScopeProvider constructor.
     *
     * @param ScopeFactory $scopeFactory
     */
    public function __construct(
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
        return $this->scopeFactory->create(Store::STORE_ID, Store::DEFAULT_STORE_ID, null);
    }
}
