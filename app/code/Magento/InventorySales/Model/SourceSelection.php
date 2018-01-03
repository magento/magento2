<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySales\Api\SourceSelectionInterface;

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
     * SourceSelection constructor.
     *
     * @param string $sourceCode
     * @param float $qty
     */
    public function __construct(string $sourceCode, float $qty)
    {
        $this->sourceCode = $sourceCode;
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
    public function setSourceCode(string $sourceCode)
    {
        $this->sourceCode = $sourceCode;
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
    public function setQty(float $qty)
    {
        $this->qty = $qty;
    }
}
