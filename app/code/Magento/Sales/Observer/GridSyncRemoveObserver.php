<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling of events and cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 * @since 2.0.0
 */
class GridSyncRemoveObserver implements ObserverInterface
{
    /**
     * Entity grid model.
     *
     * @var \Magento\Sales\Model\ResourceModel\GridInterface
     * @since 2.0.0
     */
    protected $entityGrid;

    /**
     * @param \Magento\Sales\Model\ResourceModel\GridInterface $entityGrid
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->entityGrid->purge($observer->getDataObject()->getId());
    }
}
