<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;

/**
 * @inheritdoc
 */
class ItemToSell implements ItemToSellInterface
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
     * @param string $sku
     * @param float $qty
     */
    public function __construct(string $sku, float $qty)
    {
        $this->sku = $sku;
        $this->qty = $qty;
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
    public function setSku(string $sku)
    {
        $this->sku = $sku;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity(float $qty)
    {
        $this->qty = $qty;
    }
}
