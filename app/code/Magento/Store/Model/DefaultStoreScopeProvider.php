<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\Model\Entity\ScopeProviderInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Model\Entity\ScopeFactory;

/**
 * Class StoreScope
 * @since 2.1.0
 */
class DefaultStoreScopeProvider implements ScopeProviderInterface
{

    /**
     * @var ScopeFactory
     * @since 2.1.0
     */
    private $scopeFactory;

    /**
     * StoreScopeProvider constructor.
     *
     * @param ScopeFactory $scopeFactory
     * @since 2.1.0
     */
    public function __construct(
        ScopeFactory $scopeFactory
    ) {
        $this->scopeFactory = $scopeFactory;
    }

    /**
     * @param string $entityType
     * @param array $entityData
     * @return \Magento\Framework\Model\Entity\ScopeInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function getContext($entityType, $entityData = [])
    {
        return $this->scopeFactory->create(Store::STORE_ID, Store::DEFAULT_STORE_ID, null);
    }
}
