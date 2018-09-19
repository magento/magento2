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
 */
class GridSyncInsertObserver implements ObserverInterface
{
    /**
     * Entity grid model.
     *
     * @var \Magento\Sales\Model\ResourceModel\GridInterface
     */
    protected $entityGrid;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @param \Magento\Sales\Model\ResourceModel\GridInterface $entityGrid
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\GridInterface $entityGrid,
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
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->globalConfig->getValue('dev/grid/async_indexing')) {
            $this->entityGrid->refresh($observer->getObject()->getId());
        }
    }
}
