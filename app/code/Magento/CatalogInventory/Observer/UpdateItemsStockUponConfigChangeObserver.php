<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * Catalog inventory module observer
 */
class UpdateItemsStockUponConfigChangeObserver
{
    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock
     */
    protected $resourceStock;

    /**
     * @param \Magento\CatalogInventory\Model\Resource\Stock $resourceStock
     */
    public function __construct(\Magento\CatalogInventory\Model\Resource\Stock $resourceStock)
    {
        $this->resourceStock = $resourceStock;
    }

    /**
     * Update items stock status and low stock date.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function invoke(EventObserver $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $this->resourceStock->updateSetOutOfStock($website);
        $this->resourceStock->updateSetInStock($website);
        $this->resourceStock->updateLowStockDate($website);
    }
}
