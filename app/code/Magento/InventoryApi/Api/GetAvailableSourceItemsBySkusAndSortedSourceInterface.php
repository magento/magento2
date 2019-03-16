<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Retrieve source items for a defined set of skus and sorted source codes
 *
 * Useful for determining availability and source selection
 *
 * @api
 */
interface GetAvailableSourceItemsBySkusAndSortedSourceInterface
{
    /**
     * Get Source items assigned toa set of sorted source codes and return respecting the source codes order
     *
     * @param array $skus
     * @param array $sortedSourceCodes
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    public function execute(array $skus, array $sortedSourceCodes): array;
}
