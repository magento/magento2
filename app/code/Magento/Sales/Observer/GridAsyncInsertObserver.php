<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling of events related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class GridAsyncInsertObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\GridAsyncInsert
     */
    protected $asyncInsert;

    /**
     * @param \Magento\Sales\Model\GridAsyncInsert $asyncInsert
     */
    public function __construct(
        \Magento\Sales\Model\GridAsyncInsert $asyncInsert
    ) {
        $this->asyncInsert = $asyncInsert;
    }

    /**
     * Handles asynchronous insertion of the new entity into
     * corresponding grid during cron job.
     *
     * Also method is used in the next events:
     *
     * - config_data_dev_grid_async_indexing_disabled
     *
     * Works only if asynchronous grid indexing is enabled
     * in global settings.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->asyncInsert->asyncInsert();
    }
}
