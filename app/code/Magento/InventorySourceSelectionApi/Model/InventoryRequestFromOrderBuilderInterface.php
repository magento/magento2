<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface;

/**
 * Interface InventoryRequestFromOrderBuilderInterface
 *
 * @api
 */
interface InventoryRequestFromOrderBuilderInterface
{
    /**
     * Create an inventory request from one order and a set of items
     *
     * @param int $stockId
     * @param int $orderId
     * @param ItemRequestInterface[] $requestItems
     * @return InventoryRequestInterface
     */
    public function execute(int $stockId, int $orderId, array $requestItems): InventoryRequestInterface;
}
