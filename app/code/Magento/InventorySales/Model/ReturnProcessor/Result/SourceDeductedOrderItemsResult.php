<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor\Result;

use Magento\InventorySales\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResultInterface;
use Magento\InventorySales\Model\ReturnProcessor\Result\SourceDeductedOrderItemInterface;

class SourceDeductedOrderItemsResult implements SourceDeductedOrderItemsResultInterface
{
    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var SourceDeductedOrderItemInterface[]
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
     * @inheritdoc
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
