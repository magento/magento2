<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceDeduction\Request;

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
     * @param int $stockId
     * @param string $sourceCode
     * @param array $items
     */
    public function __construct(int $stockId, string $sourceCode, array $items)
    {
        $this->stockId = $stockId;
        $this->sourceCode = $sourceCode;
        $this->items = $items;
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
}
