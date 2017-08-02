<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Catalog inventory module observer
 * @since 2.0.0
 */
class DisplayProductStatusInfoObserver implements ObserverInterface
{
    /**
     * @var StockConfigurationInterface
     * @since 2.0.0
     */
    protected $stockConfiguration;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @since 2.0.0
     */
    public function __construct(StockConfigurationInterface $stockConfiguration)
    {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Detects whether product status should be shown
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        $info = $observer->getEvent()->getStatus();
        $info->setDisplayStatus($this->stockConfiguration->isDisplayProductStockStatus());
    }
}
