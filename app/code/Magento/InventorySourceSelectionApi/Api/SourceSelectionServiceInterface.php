<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api;

use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;

/**
 * Returns source selection algorithm result for given Inventory Request
 *
 * @api
 */
interface SourceSelectionServiceInterface
{
    /**
     * @param InventoryRequestInterface $inventoryRequest
     * @param string $algorithmCode
     * @return SourceSelectionResultInterface
     */
    public function execute(
        InventoryRequestInterface $inventoryRequest,
        string $algorithmCode
    ): SourceSelectionResultInterface;
}
