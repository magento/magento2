<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;

/**
 * Returns source selection algorithm result for given Inventory Request
 * Current interface should be implemented in order to add own Source Selection Method
 *
 * @api
 */
interface SourceSelectionInterface
{
    /**
     * @param InventoryRequestInterface $inventoryRequest
     * @return SourceSelectionResultInterface
     */
    public function execute(InventoryRequestInterface $inventoryRequest): SourceSelectionResultInterface;
}
