<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\StockRepository\SalesChannel;

use Magento\InventoryApi\Api\Data\StockSearchResultsInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class DataAfterGetListPlugin
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
     * Enrich the given Stock Objects with the assigned sales channel entities
     *
     * @param StockRepositoryInterface $subject
     * @param StockSearchResultsInterface $result
     * @return StockSearchResultsInterface
     */
    public function afterGetList(
        StockRepositoryInterface $subject,
        StockSearchResultsInterface $result
    ): StockSearchResultsInterface {

        $stocks = [];
        foreach ($result->getItems() as $item) {
            $stocks[] = $this->addExtensionAttributeToStock->addAttributeToStock($item);
        }
        $result->setItems($stocks);
        return $result;
    }
}
