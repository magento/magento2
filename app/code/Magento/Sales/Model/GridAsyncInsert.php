<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling of events and cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class GridAsyncInsert
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
