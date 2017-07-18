<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryApi\Api\Command;

/**
 * Interface to assign source id list by stock id.
 *
 * @api
 */
interface AssignSourcesToStockInterface
{

    /**
     * Assign source id list by stock id.
     *
     * @param int[] $sourceIds
     * @param int $stockId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    public function execute(array $sourceIds, $stockId);
}
