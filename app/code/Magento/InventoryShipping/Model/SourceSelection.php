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
class SourceSelection implements SourceSelectionInterface
{
    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var SourceItemSelectionInterface[]
     */
    private $sourceItemSelections;

    /**
     * @param string $sourceCode
     * @param SourceItemSelectionInterface[] $sourceItemSelections
     */
    public function __construct(string $sourceCode, array $sourceItemSelections)
    {
        $this->sourceCode = $sourceCode;
        $this->sourceItemSelections = $sourceItemSelections;
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
    public function getSourceItemSelections(): array
    {
        return $this->sourceItemSelections;
    }
}
