<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier\SourceItems;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

/**
 * Check stock should be managed for given product sku.
 */
class ManageStock
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
     * ManageStock constructor.
     *
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
     * Check, if stock should be managed for give product.
     *
     * @param string $sku
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $sku = null): bool
    {
        $stockId = $this->defaultStockProvider->getId();
        if ($sku) {
            $itemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
            if ($itemConfiguration) {
                return $itemConfiguration->isUseConfigManageStock()
                    ? (bool)$this->config->getValue(Configuration::XML_PATH_MANAGE_STOCK)
                    : $itemConfiguration->isManageStock();
            }
        }

        return (bool)$this->config->getValue(Configuration::XML_PATH_MANAGE_STOCK);
    }
}
