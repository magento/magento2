<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

/**
 * Class \Magento\Store\Model\StoreResolver\Website
 *
 * @since 2.0.0
 */
class Website implements ReaderInterface
{
    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     * @since 2.0.0
     */
    protected $websiteRepository;

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
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAllowedStoreIds($scopeCode)
    {
        $stores = [];
        $website = $scopeCode ? $this->websiteRepository->get($scopeCode) : $this->websiteRepository->getDefault();
        foreach ($this->storeRepository->getList() as $store) {
            if ($store->isActive() && $store->getWebsiteId() == $website->getId()) {
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
        $website = $scopeCode ? $this->websiteRepository->get($scopeCode) : $this->websiteRepository->getDefault();
        return $this->groupRepository->get($website->getDefaultGroupId())->getDefaultStoreId();
    }
}
