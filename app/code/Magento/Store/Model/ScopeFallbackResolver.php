<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeFallbackResolverInterface;

class ScopeFallbackResolver implements ScopeFallbackResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $fallback = [];

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getFallbackScope($scope, $scopeId, $forConfig = true)
    {
        if (!isset($this->fallback[$scope][$scopeId][$forConfig])) {
            $fallback = [null, null];
            switch ($scope) {
                case ScopeInterface::SCOPE_WEBSITE:
                case ScopeInterface::SCOPE_WEBSITES:
                    $fallback = [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null];
                    break;
                case ScopeInterface::SCOPE_GROUP:
                    $fallback = [
                        ScopeInterface::SCOPE_WEBSITES,
                        $this->storeManager->getGroup($scopeId)->getWebsiteId()
                    ];
                    break;
                case ScopeInterface::SCOPE_STORE:
                case ScopeInterface::SCOPE_STORES:
                    $fallback = $forConfig
                        ? [ScopeInterface::SCOPE_WEBSITES, $this->storeManager->getStore($scopeId)->getWebsiteId()]
                        : [ScopeInterface::SCOPE_GROUP, $this->storeManager->getStore($scopeId)->getStoreGroupId()];
            }
            $this->fallback[$scope][$scopeId][$forConfig] = $fallback;
        }
        return $this->fallback[$scope][$scopeId][$forConfig];
    }
}
