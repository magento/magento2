<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Store;

use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\ScopeSwitcherInterface;

class ScopeSwitcher implements ScopeSwitcherInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function switch(ScopeInterface $scope): ScopeInterface
    {
        $fromStore = $this->storeManager->getStore();
        switch ($scope->getScopeType()) {
            case \Magento\Store\Model\ScopeInterface::SCOPE_STORE:
            case \Magento\Store\Model\ScopeInterface::SCOPE_STORES:
                $toStore = $scope->getId();
                break;
            case \Magento\Store\Model\ScopeInterface::SCOPE_GROUP:
            case \Magento\Store\Model\ScopeInterface::SCOPE_GROUPS:
                $toStore = $this->storeManager->getGroup($scope->getCode())->getDefaultStoreId();
                break;
            case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE:
            case \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES:
                $groupId = $this->storeManager->getWebsite($scope->getCode())->getDefaultGroupId();
                $toStore = $this->storeManager->getGroup($groupId)->getDefaultStoreId();
                break;
            default:
                throw new \InvalidArgumentException('Invalid scope');
        }
        $this->storeManager->setCurrentStore($toStore);
        return $fromStore;
    }
}
