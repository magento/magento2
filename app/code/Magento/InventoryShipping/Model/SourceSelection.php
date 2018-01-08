<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

class SourceSelection implements SourceSelectionInterface
{
    /**
     * @var string
     */
    private $sourceCode;

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
     * SourceSelection constructor.
     *
     * @param string $sku
     * @param string $sourceCode
     * @param float $qty
     * @param float $qtyAvailable
     */
    public function __construct(string $sku, string $sourceCode, float $qty, float $qtyAvailable)
    {
        $this->sku = $sku;
        $this->sourceCode = $sourceCode;
        $this->qty = $qty;
        $this->qtyAvailable = $qtyAvailable;
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
