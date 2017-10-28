<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\StockRepository\SalesChannel;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySales\Model\ResourceModel\ReplaceSalesChannelsOnStock;

/**
 * TODO
 */
class DataAfterSavePlugin
{
    /**
     * @var ReplaceSalesChannelsOnStock
     */
    private $replaceSalesChannelsOnStock;

    /**
     * @param ReplaceSalesChannelsOnStock $replaceSalesChannelsOnStock
     */
    public function __construct(
        ReplaceSalesChannelsOnStock $replaceSalesChannelsOnStock
    ) {
        $this->replaceSalesChannelsOnStock = $replaceSalesChannelsOnStock;
    }

    /**
     * Saves sales channel for given stock.
     *
     * @param StockRepositoryInterface $subject
     * @param StockInterface $stock
     * @return StockInterface
     */
    public function afterSave(StockRepositoryInterface $subject, StockInterface $stock): StockInterface
    {
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        if (null !== $salesChannels) {
            $this->replaceSalesChannelsOnStock->execute($salesChannels, $stock->getStockId());
        }
        return $stock;
    }
}
