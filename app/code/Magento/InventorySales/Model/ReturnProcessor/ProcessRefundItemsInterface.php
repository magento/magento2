<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\InventorySales\Model\ReturnProcessor\Request\ItemsToRefundInterface;

/**
 * Refund Items
 * @api
 */
interface ProcessRefundItemsInterface
{
    /**
     * @param OrderInterface $order
     * @param ItemsToRefundInterface[] $itemsToRefund
     * @param array $returnToStockItems
     * @return void
     */
    public function execute(
        OrderInterface $order,
        array $itemsToRefund,
        array $returnToStockItems
    );
}
