<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * Service return sales channels witch assigned to stock in records in DB,
 * but stock itself might not to has them
 */
class GetUnassignedSalesChannelsForStock
{
    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $getAssignedSalesChannelsForStock;

    /**
     * @param GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
     */
    public function __construct(
        GetAssignedSalesChannelsForStockInterface $getAssignedSalesChannelsForStock
    ) {
        $this->getAssignedSalesChannelsForStock = $getAssignedSalesChannelsForStock;
    }

    /**
     * Return all sales channels witch will be unassigned from the saved stock
     *
     * @param StockInterface $stock
     * @return \Magento\InventorySales\Model\SalesChannel[]
     */
    public function execute(StockInterface $stock): array
    {
        $newWebsiteCodes = $result = [];
        $assignedSalesChannels = $this->getAssignedSalesChannelsForStock->execute((int)$stock->getStockId());
        $extensionAttributes = $stock->getExtensionAttributes();
        $newSalesChannels = $extensionAttributes->getSalesChannels() ?: [];

        foreach ($newSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE) {
                $newWebsiteCodes[] = $salesChannel->getCode();
            }
        }

        foreach ($assignedSalesChannels as $salesChannel) {
            if ($salesChannel->getType() === SalesChannel::TYPE_WEBSITE
                && !in_array($salesChannel->getCode(), $newWebsiteCodes, true)
            ) {
                $result[] = $salesChannel;
            }
        }

        return $result;
    }
}
