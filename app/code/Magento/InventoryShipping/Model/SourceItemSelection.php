<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

/**
 * @inheritdoc
 */
class SourceItemSelection implements SourceItemSelectionInterface
{
    /**
     * @var float
     */
    private $qty;

    /**
     * @var float
     */
    private $qtyAvailable;

    /**
     * @var string
     */
    private $sku;

    /**
     * @param string $sku
     * @param float $qty
     * @param float $qtyAvailable
     */
    public function __construct(string $sku, float $qty, float $qtyAvailable)
    {
        $this->sku = $sku;
        $this->qty = $qty;
        $this->qtyAvailable = $qtyAvailable;
    }

    /**
     * @inheritdoc
     */
    public function getQty(): float
    {
        return $this->qty;
    }

    /**
     * @inheritdoc
     */
    public function getQtyAvailable(): float
    {
        return $this->qtyAvailable;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }
}
