<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Unassign source from stock command
 *
 * @api
 */
interface UnassignSourceFromStockInterface
{
    /**
     * Unassign source from stock
     *
     * @param int $stockId
     * @param int $sourceId
     * @return void
     */
    public function execute($stockId, $sourceId);
}
