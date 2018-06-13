<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor\Request;

class BackItemQtyRequest
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
    private $qty;

    /**
     * @param string $sourceCode
     * @param string $sku
     * @param float $qty
     */
    public function __construct(string $sourceCode, string $sku, float $qty)
    {
        $this->sourceCode = $sourceCode;
        $this->sku = $sku;
        $this->qty = $qty;
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
    public function getQuantity(): float
    {
        return $this->qty;
    }
}
