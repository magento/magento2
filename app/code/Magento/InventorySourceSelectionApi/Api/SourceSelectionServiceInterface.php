<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api;

/**
 * Returns source selection algorithm result for given Inventory Request
 *
 * @api
 */
interface SourceSelectionServiceInterface
{
    /**
     * @param \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface $inventoryRequest
     * @param string $algorithmCode
     * @return \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface
     */
    public function execute(
        \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface $inventoryRequest,
        string $algorithmCode
    ): \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
}
