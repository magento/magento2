<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceSelection\Request;

use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface;

/**
 * @inheritdoc
 */
class InventoryRequest implements InventoryRequestInterface
{
    /**
     * @var int
     */
    private $stockId;

    /**
     * @var ItemRequestInterface[]
     */
    private $items;

    /**
     * @param int $stockId
     * @param array $items
     */
    public function __construct(int $stockId, array $items)
    {
        $this->stockId = $stockId;
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
    public function getItems(): array
    {
        return $this->items;
    }
}
