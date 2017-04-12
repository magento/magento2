<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Cron;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class GridAsyncInsert
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
     * @return void
     */
    public function execute()
    {
        $this->asyncInsert->asyncInsert();
    }
}
