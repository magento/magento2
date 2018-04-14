<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceDeduction\Request;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * @inheritdoc
 */
class SourceDeductionRequest implements SourceDeductionRequestInterface
{
    /**
     * @var int
     */
    private $stockId;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var ItemToDeductInterface[]
     */
    private $items;

    /**
     * @var SalesEventInterface
     */
    private $salesEvent;

    /**
     * @param int $stockId
     * @param string $sourceCode
     * @param array $items
     * @param SalesEventInterface $salesEvent
     */
    public function __construct(int $stockId, string $sourceCode, array $items, SalesEventInterface $salesEvent)
    {
        $this->stockId = $stockId;
        $this->sourceCode = $sourceCode;
        $this->items = $items;
        $this->salesEvent = $salesEvent;
    }

    /**
     * @inheritdoc
     */
    public function getStockId(): int
    {
        return $this->stockId;
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

    /**
     * @inheritdoc
     */
    public function getSalesEvent(): SalesEventInterface
    {
        return $this->salesEvent;
    }
}
