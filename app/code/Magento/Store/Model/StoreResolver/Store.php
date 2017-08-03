<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

/**
 * Class \Magento\Store\Model\StoreResolver\Store
 *
 * @since 2.0.0
 */
class Store implements ReaderInterface
{
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     * @since 2.0.0
     */
    protected $storeRepository;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @since 2.0.0
     */
    public function __construct(\Magento\Store\Api\StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getDefaultStoreId($scopeCode)
    {
        return $this->storeRepository->get($scopeCode)->getId();
    }
}
