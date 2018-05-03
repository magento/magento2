<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceDeduction\Request;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * Request products in a given Qty, sourceCode and StockId
 *
 * @api
 */
interface SourceDeductionRequestInterface
{
    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @return string
     */
    public function getSourceCode(): string;

    /**
     * @return ItemToDeductInterface[]
     */
    public function getItems(): array;

    /**
     * @return SalesEventInterface
     */
    public function getSalesEvent(): SalesEventInterface;
}
