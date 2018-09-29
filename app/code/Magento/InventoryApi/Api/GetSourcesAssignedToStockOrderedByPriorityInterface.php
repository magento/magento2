<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Retrieve sources related to current stock ordered by priority
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface GetSourcesAssignedToStockOrderedByPriorityInterface
{
    /**
     * Get Sources assigned to Stock ordered by priority
     *
     * If Stock with given id doesn't exist then return an empty array
     *
     * @param int $stockId
     * @return \Magento\InventoryApi\Api\Data\SourceInterface[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(int $stockId): array;
}
