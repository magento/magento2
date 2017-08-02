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
 * @since 2.0.0
 */
class GridAsyncInsert
{
    /**
     * @var \Magento\Sales\Model\GridAsyncInsert
     * @since 2.0.0
     */
    protected $asyncInsert;

    /**
     * @param \Magento\Sales\Model\GridAsyncInsert $asyncInsert
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->asyncInsert->asyncInsert();
    }
}
