<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Config for category in the layered navigation
 */
class LayerCategoryConfig
{
    private const XML_PATH_CATALOG_LAYERED_NAVIGATION_DISPLAY_CATEGORY = 'catalog/layered_navigation/display_category';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * LayerCategoryConfig constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Check if category filter item should be added in the layered navigation
     *
     * @param string $scopeType
     * @param null|int|string $scopeCode
     *
     * @return bool
     */
    public function isCategoryFilterVisibleInLayerNavigation(
        $scopeType = ScopeInterface::SCOPE_STORES,
        $scopeCode = null
    ): bool {
        if (!$scopeCode) {
            $scopeCode = $this->getStoreId();
        }

        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_CATALOG_LAYERED_NAVIGATION_DISPLAY_CATEGORY,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Get the current store ID
     *
     * @return int
     *
     * @throws NoSuchEntityException
     */
    private function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }
}
