<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Reader implementation for website.
 */
class Website implements ReaderInterface
{
    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        protected readonly StoreRepositoryInterface $storeRepository,
        protected readonly WebsiteRepositoryInterface $websiteRepository,
        protected readonly GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getAllowedStoreIds($scopeCode)
    {
        $stores = [];
        $website = $scopeCode ? $this->websiteRepository->get($scopeCode) : $this->websiteRepository->getDefault();
        foreach ($this->storeRepository->getList() as $store) {
            if ($store->getIsActive()) {
                if (($scopeCode && $store->getWebsiteId() == $website->getId()) || (!$scopeCode)) {
                    $stores[$store->getId()] = $store->getId();
                }
            }
        }
        sort($stores);
        return $stores;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultStoreId($scopeCode)
    {
        $website = $scopeCode ? $this->websiteRepository->get($scopeCode) : $this->websiteRepository->getDefault();
        return $this->groupRepository->get($website->getDefaultGroupId())->getDefaultStoreId();
    }
}
