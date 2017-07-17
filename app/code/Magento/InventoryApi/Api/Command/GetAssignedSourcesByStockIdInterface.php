<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Command;

/**
 * Interface to get assign sources by stock.
 *
 * @api
 */
interface GetAssignedSourcesByStockIdInterface
{
    /**
     * Get sources assigned to stock
     *
     * @param int $stockId
     * @return \Magento\InventoryApi\Api\Data\SourceStockLinkInterface[]
     */
    public function execute($stockId);

}
