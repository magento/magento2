<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\StockRepository\SalesChannel;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class DataAfterGetPlugin
{
    /**
     * @var AddSalesChannelsToStock
     */
    private $addExtensionAttributeToStock;

    /**
     * @param AddSalesChannelsToStock $addSalesChannelsToStock
     */
    public function __construct(
        AddSalesChannelsToStock $addSalesChannelsToStock
    ) {
        $this->addExtensionAttributeToStock = $addSalesChannelsToStock;
    }

    /**
     * Enrich the given Stock Object with the assigned sales channel entities
     *
     * @param StockRepositoryInterface $subject
     * @param StockInterface $result
     * @return StockInterface
     */
    public function afterGet(StockRepositoryInterface $subject, StockInterface $result): StockInterface
    {
        return $this->addExtensionAttributeToStock->addAttributeToStock($result);
    }
}
