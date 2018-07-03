<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor\Request;

use Magento\InventorySalesApi\Model\ReturnProcessor\Request\ItemsToRefundInterface;

class ItemsToRefund implements ItemsToRefundInterface
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $qty;

    /**
     * @var float
     */
    private $processedQty;

    /**
     * @param string $sku
     * @param float $qty
     * @param float $processedQty
     */
    public function __construct(string $sku, float $qty, float $processedQty)
    {
        $this->sku = $sku;
        $this->qty = $qty;
        $this->processedQty = $processedQty;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity(): float
    {
        return $this->qty;
    }

    /**
     * @inheritdoc
     */
    public function getProcessedQuantity(): float
    {
        return $this->processedQty;
    }
}
