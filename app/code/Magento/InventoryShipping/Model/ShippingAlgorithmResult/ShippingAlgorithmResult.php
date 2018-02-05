<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\ShippingAlgorithmResult;

/**
 * @inheritdoc
 */
class ShippingAlgorithmResult implements ShippingAlgorithmResultInterface
{
    /**
     * @var SourceSelectionInterface[]
     */
    private $sourceSelections;

    /**
     * @var bool
     */
    private $isShippable;

    /**
     * @param SourceSelectionInterface[] $sourceSelections
     */
    public function __construct(array $sourceSelections, bool $isShippable)
    {
        $this->sourceSelections = $sourceSelections;
        $this->isShippable = $isShippable;
    }

    /**
     * @inheritdoc
     */
    public function getSourceSelections(): array
    {
        return $this->sourceSelections;
    }

    /**
     * @inheritdoc
     */
    public function isShippable(): bool
    {
        return $this->isShippable;
    }
}
