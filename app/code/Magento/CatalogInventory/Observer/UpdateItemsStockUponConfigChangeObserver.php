<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;

/**
 * Catalog inventory module observer
 */
class UpdateItemsStockUponConfigChangeObserver implements ObserverInterface
{
    /**
     * @var Item
     */
    protected $resourceStockItem;

    /**
     * @param Item $resourceStockItem
     */
    public function __construct(Item $resourceStockItem)
    {
        $this->resourceStockItem = $resourceStockItem;
    }

    /**
     * Update items stock status and low stock date.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $website = (int) $observer->getEvent()->getWebsite();
        $this->resourceStockItem->updateSetOutOfStock($website);
        $this->resourceStockItem->updateSetInStock($website);
        $this->resourceStockItem->updateLowStockDate($website);
    }
}
