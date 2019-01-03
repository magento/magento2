<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

/**
 * Reader implementation for website.
 */
class Website implements ReaderInterface
{
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

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
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
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
