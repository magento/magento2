<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceDeductionApi\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Request products in a given Qty, sourceCode and StockId
 *
 * @api
 */
interface SourceDeductionRequestInterface
{
    /**
     * @return string
     */
    public function getSourceCode(): string;

    /**
     * @return ItemToDeductInterface[]
     */
    public function getItems(): array;

    /**
     * @return SalesChannelInterface
     */
    public function getSalesChannel(): SalesChannelInterface;

    /**
     * @return SalesEventInterface
     */
    public function getSalesEvent(): SalesEventInterface;
}
