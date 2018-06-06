<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor\Result;

/**
 * DTO used as the type for values of `$items` array passed in SourceDeductedOrderItemsResultInterface
 *
 * @api
 */
interface SourceDeductedOrderItemInterface
{
    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @return float
     */
    public function getQuantity(): float;
}
