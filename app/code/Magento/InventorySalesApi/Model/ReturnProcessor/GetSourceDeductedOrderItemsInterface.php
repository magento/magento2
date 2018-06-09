<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model\ReturnProcessor;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResult;

/**
 * Service which return deducted items per source
 *
 * @api
 */
interface GetSourceDeductedOrderItemsInterface
{
    /**
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @return SourceDeductedOrderItemsResult[]
     */
    public function execute(OrderInterface $order, array $returnToStockItems): array;
}
