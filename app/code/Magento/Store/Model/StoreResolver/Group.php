<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class Group implements ReaderInterface
{
    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        protected readonly StoreRepositoryInterface $storeRepository,
        protected readonly GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedStoreIds($scopeCode)
    {
        $stores = [];
        foreach ($this->storeRepository->getList() as $store) {
            if ($store->isActive() && (int) $store->getGroupId() === $scopeCode) {
                $stores[] = $store->getId();
            }
        }
        return $stores;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultStoreId($scopeCode)
    {
        return $this->groupRepository->get($scopeCode)->getDefaultStoreId();
    }
}
