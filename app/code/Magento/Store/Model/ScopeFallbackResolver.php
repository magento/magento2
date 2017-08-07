<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeFallbackResolverInterface;

/**
 * Class \Magento\Store\Model\ScopeFallbackResolver
 *
 * @since 2.1.0
 */
class ScopeFallbackResolver implements ScopeFallbackResolverInterface
{
    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $fallback = [];

    /**
     * @param StoreManagerInterface $storeManager
     * @since 2.1.0
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
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
