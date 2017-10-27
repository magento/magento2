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
     * @var AddExtensionAttributeToStock
     */
    private $addExtensionAttributeToStock;

    /**
     * SalesChannelDataAfterGetPlugin constructor.
     *
     * @param AddExtensionAttributeToStock $addExtensionAttributeToStock
     * @internal param GetSalesChannelsByStockInterface $getSalesChannelByStock
     */
    public function __construct(
        AddExtensionAttributeToStock $addExtensionAttributeToStock
    ) {
        $this->addExtensionAttributeToStock = $addExtensionAttributeToStock;
    }

    /**
     * Enrich the given Stock Objects with the assigned sales channel entitys
     *
     * @param StockRepositoryInterface $subject
     * @param StockSearchResultsInterface $result
     * @return StockSearchResultsInterface
     */
    public function afterGetList(StockRepositoryInterface $subject, StockSearchResultsInterface $result): StockSearchResultsInterface
    {
        $items = $result->getItems();
        $stockItems = [];
        foreach($items as $item)
        {
            $stockItems[] = $this->addExtensionAttributeToStock->addAttributeToStock($item);
        }
        $result->setItems($stockItems);
        return  $result;
    }
}
