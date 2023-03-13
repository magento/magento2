<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

use Magento\Store\Api\StoreRepositoryInterface;

class Store implements ReaderInterface
{
    /**
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        protected readonly StoreRepositoryInterface $storeRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedStoreIds($scopeCode)
    {
        $stores = [];
        foreach ($this->storeRepository->getList() as $store) {
            if ($store->isActive()) {
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
        return $this->storeRepository->get($scopeCode)->getId();
    }
}
