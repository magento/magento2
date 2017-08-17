<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

/**
 * Class \Magento\Store\Model\StoreResolver\Group
 *
 */
class Group implements ReaderInterface
{
    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
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
