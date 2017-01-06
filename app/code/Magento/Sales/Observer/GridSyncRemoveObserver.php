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
 * Performs handling of events and cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class GridSyncRemoveObserver implements ObserverInterface
{
    /**
     * Entity grid model.
     *
     * @var \Magento\Sales\Model\ResourceModel\GridInterface
     */
    protected $entityGrid;

    /**
     * @param \Magento\Sales\Model\ResourceModel\GridInterface $entityGrid
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\GridInterface $entityGrid
    ) {
        $this->entityGrid = $entityGrid;
    }

    /**
     * Handles synchronous removing of the entity from
     * corresponding grid on certain events.
     *
     * Used in the next events:
     *
     *  - sales_order_delete_after
     *  - sales_order_invoice_delete_after
     *  - sales_order_shipment_delete_after
     *  - sales_order_creditmemo_delete_after
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->entityGrid->purge($observer->getDataObject()->getId());
    }
}
