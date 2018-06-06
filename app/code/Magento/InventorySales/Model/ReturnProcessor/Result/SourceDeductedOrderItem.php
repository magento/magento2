<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor\Result;

use Magento\InventorySales\Model\ReturnProcessor\Result\SourceDeductedOrderItemInterface;

class SourceDeductedOrderItem implements SourceDeductedOrderItemInterface
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @param string $sku
     * @param float $quantity
     */
    public function __construct(string $sku, float $quantity)
    {
        $this->sku = $sku;
        $this->quantity = $quantity;
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
        return $this->quantity;
    }
}
