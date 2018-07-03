<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Model;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

/**
 * Check source items should be managed for given product sku
 */
class CanManageSourceItemsBySku
{
    /**
     * Provides manage stock global config value.
     *
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Provides default stock id for current website in order to get correct stock configuration for product.
     *
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * Provides stock item configuration for given product sku.
     *
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param ScopeConfigInterface $config
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ScopeConfigInterface $config,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->config = $config;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * @param string $sku Sku can be null if product is new
     * @return bool
     */
    public function execute(string $sku = null): bool
    {
        if (null !== $sku) {
            $stockId = $this->defaultStockProvider->getId();
            $itemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

            return $itemConfiguration->isUseConfigManageStock()
                ? (bool)$this->config->getValue(Configuration::XML_PATH_MANAGE_STOCK)
                : $itemConfiguration->isManageStock();
        }

        return (bool)$this->config->getValue(Configuration::XML_PATH_MANAGE_STOCK);
    }
}
