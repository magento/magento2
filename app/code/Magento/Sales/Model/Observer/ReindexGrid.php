<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

/**
 * Sales entity grids re-indexing observer.
 *
 * Performs handling of events and cron jobs related to re-indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class ReindexGrid
{
    /**
     * Entity grid model.
     *
     * @var \Magento\Sales\Model\Resource\GridInterface
     */
    protected $entityGrid;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @param \Magento\Sales\Model\Resource\GridInterface $entityGrid
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     */
    public function __construct(
        \Magento\Sales\Model\Resource\GridInterface $entityGrid,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
    ) {
        $this->entityGrid = $entityGrid;
        $this->globalConfig = $globalConfig;
    }

    /**
     * Handles synchronous insertion of the new entity into
     * corresponding grid on certain events.
     *
     * Used in the next events:
     *
     *  - sales_order_resource_save_after
     *  - sales_order_invoice_resource_save_after
     *  - sales_order_shipment_resource_save_after
     *  - sales_order_creditmemo_resource_save_after
     *
     * Works only if synchronous grid re-indexing is enabled
     * in global settings.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function syncInsert(\Magento\Framework\Event\Observer $observer)
    {
        //TODO: Replace path to real configuration path after MAGETWO-35147 is complete.
        if (!$this->globalConfig->getValue('path/to/value/sync_grid_indexing')) {
            $this->entityGrid->refresh($observer->getEntity()->getId());
        }
    }

    /**
     * Handles synchronous removing of the entity from
     * corresponding grid on certain events.
     *
     * Used in the next events:
     *
     *  - sales_order_resource_delete_after
     *  - sales_order_invoice_resource_delete_after
     *  - sales_order_shipment_resource_delete_after
     *  - sales_order_creditmemo_resource_delete_after
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function syncRemove(\Magento\Framework\Event\Observer $observer)
    {
        $this->entityGrid->purge($observer->getEntity()->getId());
    }

    /**
     * Handles asynchronous insertion of the new entity into
     * corresponding grid during cron job.
     *
     * Works only if synchronous grid re-indexing is disabled
     * in global settings.
     *
     * @return void
     */
    public function asyncInsert()
    {
        //TODO: Replace path to real configuration path after MAGETWO-35147 is complete.
        if (!$this->globalConfig->getValue('path/to/value/sync_grid_indexing')) {
            $this->entityGrid->refresh();
        }
    }
}
