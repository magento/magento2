<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Interface to assign source id list by stock id
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface AssignSourcesToStockInterface
{
    /**
     * Assign source id list by stock id
     *
     * @param int $stockId
     * @param int[] $sourceIds
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute($stockId, array $sourceIds);
}
