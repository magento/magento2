<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling of events and cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class IndexGrid
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
     *  - sales_order_save_after
     *  - sales_order_invoice_save_after
     *  - sales_order_shipment_save_after
     *  - sales_order_creditmemo_save_after
     *
     * Works only if asynchronous grid indexing is disabled
     * in global settings.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function syncInsert(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->globalConfig->getValue('dev/grid/async_indexing')) {
            $this->entityGrid->refresh($observer->getObject()->getId());
        }
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
    public function syncRemove(\Magento\Framework\Event\Observer $observer)
    {
        $this->entityGrid->purge($observer->getDataObject()->getId());
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
     * @return void
     */
    public function asyncInsert()
    {
        if ($this->globalConfig->getValue('dev/grid/async_indexing')) {
            $this->entityGrid->refreshBySchedule();
        }
    }
}
