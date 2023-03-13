<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreResolver\Group as StoreResolverGroup;
use Magento\Store\Model\StoreResolver\Store as StoreResolverStore;
use Magento\Store\Model\StoreResolver\Website as StoreResolverWebsite;

class ReaderList
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $resolverMap
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        protected $resolverMap = [
            ScopeInterface::SCOPE_WEBSITE => StoreResolverWebsite::class,
            ScopeInterface::SCOPE_GROUP => StoreResolverGroup::class,
            ScopeInterface::SCOPE_STORE => StoreResolverStore::class,
        ]
    ) {
    }

    // @codingStandardsIgnoreEnd

    /**
     * Retrieve store relation reader by run mode
     *
     * @param string $runMode
     * @return ReaderInterface
     */
    public function getReader($runMode)
    {
        return $this->objectManager->get($this->resolverMap[$runMode]);
    }
}
