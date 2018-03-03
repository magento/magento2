<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceSelection\Result;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;

/**
 * @inheritdoc
 */
class SourceSelectionItem implements SourceSelectionItemInterface
{
    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $qtyToDeduct;

    /**
     * @var float
     */
    private $qtyAvailable;

    /**
     * @param string $sourceCode
     * @param string $sku
     * @param float $qtyToDeduct
     * @param float $qtyAvailable
     */
    public function __construct(string $sourceCode, string $sku, float $qtyToDeduct, float $qtyAvailable)
    {
        $this->sourceCode = $sourceCode;
        $this->sku = $sku;
        $this->qtyToDeduct = $qtyToDeduct;
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
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritdoc
     */
    public function getQtyToDeduct(): float
    {
        return $this->qtyToDeduct;
    }

    /**
     * @inheritdoc
     */
    public function getQtyAvailable(): float
    {
        return $this->qtyAvailable;
    }
}
