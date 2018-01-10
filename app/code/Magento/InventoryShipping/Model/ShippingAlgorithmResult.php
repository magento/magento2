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
class ShippingAlgorithmResult implements ShippingAlgorithmResultInterface
{
    /**
     * @var SourceSelectionInterface[]
     */
    private $sourceSelections;

    /**
     * @param SourceSelectionInterface[] $sourceSelections
     */
    public function __construct(array $sourceSelections)
    {
        $this->sourceSelections = $sourceSelections;
    }

    /**
     * @inheritdoc
     */
    public function getSourceSelections(): array
    {
        return $this->sourceSelections;
    }
}
