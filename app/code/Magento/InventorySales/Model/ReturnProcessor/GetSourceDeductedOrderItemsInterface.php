<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\InventorySales\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResultInterface;
use Magento\Sales\Model\Order as OrderModel;

/**
 * Service which return deducted items per source
 *
 * @api
 */
interface GetSourceDeductedOrderItemsInterface
{
    /**
     * @param OrderModel $order
     * @param array $returnToStockItems
     * @return SourceDeductedOrderItemsResultInterface[]
     */
    public function execute(OrderModel $order, array $returnToStockItems): array;
}
