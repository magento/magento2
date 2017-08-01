<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

/**
 * Class \Magento\Store\Model\StoreResolver\Group
 *
 * @since 2.0.0
 */
class Group implements ReaderInterface
{
    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     * @since 2.0.0
     */
    protected $groupRepository;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     * @since 2.0.0
     */
    protected $storeRepository;

    /**
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getDefaultStoreId($scopeCode)
    {
        return $this->groupRepository->get($scopeCode)->getDefaultStoreId();
    }
}
