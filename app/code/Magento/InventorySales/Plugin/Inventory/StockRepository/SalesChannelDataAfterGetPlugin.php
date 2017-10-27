<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Inventory\StockRepository;

use Magento\InventoryApi\Api\Data\StockExtension;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySales\Model\GetSalesChannelsByStockInterface;

class SalesChannelDataAfterGetPlugin
{
    /**
     * @var GetSalesChannelsByStockInterface
     */
    private $getSalesChannelByStock;



    /**
     * SalesChannelDataAfterGetPlugin constructor.
     * @param GetSalesChannelsByStockInterface $getSalesChannelByStock
     */
    public function __construct(
        GetSalesChannelsByStockInterface $getSalesChannelByStock
    ) {
        $this->getSalesChannelByStock = $getSalesChannelByStock;
    }

    public function afterGet(StockRepositoryInterface $subject, StockInterface $result): StockInterface
    {
        /** @var StockExtension $extensionAttributes */
        $extensionAttributes = $result->getExtensionAttributes();

        $salesChannelSearchResults = $this->getSalesChannelByStock->get($result->getStockId());
        $extensionAttributes->setData('sales_channels', $salesChannelSearchResults);
        $result->setExtensionAttributes($extensionAttributes);
        return  $result;
    }

   /* public function afterGetList(StockRepositoryInterface $subject,
                                 StockSearchResultsInterface $result): StockSearchResultsInterface
    {
        $items = $result->getItems();
        $item = reset($items);
        $extensionAttributes = $item->getExtensionAttributes();
        return $result;
    }*/
}