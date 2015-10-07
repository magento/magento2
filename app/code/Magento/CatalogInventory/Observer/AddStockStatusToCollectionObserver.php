<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Catalog inventory module observer
 */
class AddStockStatusToCollectionObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     */
    public function __construct(\Magento\CatalogInventory\Helper\Stock $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    /**
     * Add information about product stock status to collection
     * Used in for product collection after load
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $this->stockHelper->addStockStatusToProducts($productCollection);
    }
}
