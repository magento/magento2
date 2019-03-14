<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model\ReturnProcessor\Result;

use Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItem;

/**
 * DTO used as returned type of GetSourceDeductedOrderItemsInterface
 */
class SourceDeductedOrderItemsResult
{
    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var SourceDeductedOrderItem[]
     */
    private $items;

    /**
     * @param string $sourceCode
     * @param array $items
     */
    public function __construct(string $sourceCode, array $items)
    {
        $this->sourceCode = $sourceCode;
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    /**
     * @return SourceDeductedOrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
