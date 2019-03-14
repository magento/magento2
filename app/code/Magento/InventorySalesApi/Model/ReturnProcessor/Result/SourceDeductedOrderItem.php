<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model\ReturnProcessor\Result;

/**
 * DTO used as the type for values of `$items` array passed in SourceDeductedOrderItemsResult
 */
class SourceDeductedOrderItem
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
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }
}
