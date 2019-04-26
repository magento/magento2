<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\InventoryConfiguration\Model\GetLegacyStockItem;
use Magento\InventoryConfiguration\Model\StockItemConfigurationFactory;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

/**
 * @inheritdoc
 */
class GetStockItemConfiguration
{
    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

    /**
     * @var StockItemConfigurationFactory
     */
    private $stockItemConfigurationFactory;

    /**
     * @param GetLegacyStockItem $getLegacyStockItem
     * @param StockItemConfigurationFactory $stockItemConfigurationFactory
     */
    public function __construct(
        GetLegacyStockItem $getLegacyStockItem,
        StockItemConfigurationFactory $stockItemConfigurationFactory
    ) {
        $this->getLegacyStockItem = $getLegacyStockItem;
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku): StockItemConfigurationInterface
    {
        return $this->stockItemConfigurationFactory->create(
            [
                'stockItem' => $this->getLegacyStockItem->execute($sku)
            ]
        );
    }
}
