<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

class Store implements ReaderInterface
{
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(\Magento\Store\Api\StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
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
