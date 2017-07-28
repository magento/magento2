<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class \Magento\CatalogInventory\Observer\AddInventoryDataObserver
 *
 * @since 2.0.0
 */
class AddInventoryDataObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     * @since 2.0.0
     */
    protected $stockHelper;

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @since 2.0.0
     */
    public function __construct(\Magento\CatalogInventory\Helper\Stock $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    /**
     * Add stock information to product
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $this->stockHelper->assignStatusToProduct($product);
        }
    }
}
