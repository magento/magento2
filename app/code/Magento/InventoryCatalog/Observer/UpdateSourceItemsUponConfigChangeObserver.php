<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

/**
 * Update source items index on global inventory configuration changes
 */
class UpdateSourceItemsUponConfigChangeObserver implements ObserverInterface
{
    /**
     * @var StockIndexer
     */
    private $stockIndexer;

    /**
     * @param StockIndexer $stockIndexer
     */
    public function __construct(StockIndexer $stockIndexer)
    {
        $this->stockIndexer = $stockIndexer;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $this->stockIndexer->executeFull();
    }
}
