<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;

class MoveStoreLevelDesignConfigToWebsiteScopeOnSingleStoreMode implements ObserverInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DesignConfigRepositoryInterface $designConfigRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly DesignConfigRepositoryInterface $designConfigRepository,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $changedPaths = (array)$observer->getEvent()->getChangedPaths();
        if (in_array(StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED, $changedPaths, true)
            && $this->scopeConfig->getValue(StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED)
        ) {
            $store = $this->storeManager->getDefaultStoreView();
            if ($store) {
                $websiteId = $store->getWebsiteId();
                $storeId = $store->getId();
                $designConfig = $this->designConfigRepository->getByScope(ScopeInterface::SCOPE_STORES, $storeId);
                // Copy design config from store scope to website scope
                $designConfig->setScope(ScopeInterface::SCOPE_WEBSITES);
                $designConfig->setScopeId($websiteId);
                $this->designConfigRepository->save($designConfig);
                // At this point store design config is the same as website design config.
                // let's delete store design config to preserve inheritance from website design config.
                $designConfig->setScope(ScopeInterface::SCOPE_STORES);
                $designConfig->setScopeId($storeId);
                $this->designConfigRepository->delete($designConfig);
            }
        }
    }
}
