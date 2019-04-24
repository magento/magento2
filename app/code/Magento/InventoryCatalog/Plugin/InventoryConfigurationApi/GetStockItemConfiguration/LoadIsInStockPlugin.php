<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryConfigurationApi\GetStockItemConfiguration;

use Magento\InventoryConfiguration\Model\GetLegacyStockItem;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

/**
 * Load Legacy Stock Item IsInStock for StockItemConfiguration
 */
class LoadIsInStockPlugin
{
    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

    /**
     * @param GetLegacyStockItem $getLegacyStockItem
     */
    public function __construct(GetLegacyStockItem $getLegacyStockItem)
    {
        $this->getLegacyStockItem = $getLegacyStockItem;
    }

    /**
     * @param GetStockItemConfigurationInterface $subject
     * @param StockItemConfigurationInterface $result
     * @param string $sku
     * @param int $stockId
     * @return StockItemConfigurationInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        GetStockItemConfigurationInterface $subject,
        StockItemConfigurationInterface $result,
        string $sku,
        int $stockId
    ): StockItemConfigurationInterface {
        $legacyStockItem = $this->getLegacyStockItem->execute($sku);
        $extensionAttributes = $result->getExtensionAttributes();
        $extensionAttributes->setIsInStock((bool)(int)$legacyStockItem->getIsInStock());
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
