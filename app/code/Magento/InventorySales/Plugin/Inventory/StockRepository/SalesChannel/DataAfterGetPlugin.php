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
     * @var AddExtensionAttributeToStock
     */
    private $addExtensionAttributeToStock;

    /**
     * SalesChannelDataAfterGetPlugin constructor.
     *
     * @param AddExtensionAttributeToStock $addExtensionAttributeToStock
     */
    public function __construct(
        AddExtensionAttributeToStock $addExtensionAttributeToStock
    ) {
        $this->addExtensionAttributeToStock = $addExtensionAttributeToStock;
    }

    /**
     * Enrich the given Stock Object with the assigned sales channel entitys
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
