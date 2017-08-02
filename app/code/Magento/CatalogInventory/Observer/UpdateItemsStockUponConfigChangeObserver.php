<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Catalog inventory module observer
 * @since 2.0.0
 */
class UpdateItemsStockUponConfigChangeObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock
     * @since 2.0.0
     */
    protected $resourceStock;

    /**
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock $resourceStock
     * @since 2.0.0
     */
    public function __construct(\Magento\CatalogInventory\Model\ResourceModel\Stock $resourceStock)
    {
        $this->resourceStock = $resourceStock;
    }

    /**
     * Update items stock status and low stock date.
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $this->resourceStock->updateSetOutOfStock($website);
        $this->resourceStock->updateSetInStock($website);
        $this->resourceStock->updateLowStockDate($website);
    }
}
