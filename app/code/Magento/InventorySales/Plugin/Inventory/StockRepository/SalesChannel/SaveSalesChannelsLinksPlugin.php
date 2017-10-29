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
class SaveSalesChannelsLinksPlugin
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
     * @param callable $proceed
     * @param StockInterface $stock
     * @return int
     */
    public function aroundSave(
        StockRepositoryInterface $subject,
        callable $proceed,
        StockInterface $stock
    ): int {
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannels = $extensionAttributes->getSalesChannels();
        $stockId = $proceed($stock);
        if (null !== $salesChannels) {
            $this->replaceSalesChannelsOnStock->execute($salesChannels, $stockId);
        }
        return $stockId;
    }
}
