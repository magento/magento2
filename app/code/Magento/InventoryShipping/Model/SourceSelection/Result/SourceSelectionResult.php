<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\SourceSelection\Result;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;

/**
 * @inheritdoc
 */
class SourceSelectionResult implements SourceSelectionResultInterface
{
    /**
     * @var SourceSelectionItemInterface[]
     */
    private $sourceItemSelections;

    /**
     * @var bool
     */
    private $isShippable;

    /**
     * @param SourceSelectionItemInterface[] $sourceItemSelections
     * @param bool $isShippable
     */
    public function __construct(array $sourceItemSelections, bool $isShippable)
    {
        $this->sourceItemSelections = $sourceItemSelections;
        $this->isShippable = $isShippable;
    }

    /**
     * @inheritdoc
     */
    public function getSourceSelectionItems(): array
    {
        return $this->sourceItemSelections;
    }

    /**
     * @inheritdoc
     */
    public function isShippable(): bool
    {
        return $this->isShippable;
    }
}
