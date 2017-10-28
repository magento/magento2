<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\StockRepository\SalesChannel;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class DataAfterSavePlugin
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
     * Saves sales channel for given stock.
     *
     * @param StockRepositoryInterface $subject
     * @param StockInterface $result
     * @return StockInterface
     */
    public function afterSave(StockRepositoryInterface $subject, StockInterface $result): StockInterface
    {
        $extensionAttributes = $$result->getExtensionAttributes();
        $salesChannelSearchResults = $this->getSalesChannelByStock->get($result->getStockId());
        $extensionAttributes->setSalesChannels($salesChannelSearchResults);
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
