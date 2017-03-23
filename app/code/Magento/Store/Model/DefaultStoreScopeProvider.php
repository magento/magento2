<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\Model\Entity\ScopeProviderInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Model\Entity\ScopeFactory;

/**
 * Class StoreScope
 */
class DefaultStoreScopeProvider implements ScopeProviderInterface
{

    /**
     * @var ScopeFactory
     */
    private $scopeFactory;

    /**
     * StoreScopeProvider constructor.
     *
     * @param ScopeFactory $scopeFactory
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
     */
    public function getContext($entityType, $entityData = [])
    {
        return $this->scopeFactory->create(Store::STORE_ID, Store::DEFAULT_STORE_ID, null);
    }
}
