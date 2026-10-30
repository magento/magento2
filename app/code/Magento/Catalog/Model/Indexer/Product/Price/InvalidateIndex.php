<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Invalidate price index
 */
class InvalidateIndex implements UpdateIndexInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * Constructor
     *
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(GroupInterface $group, $isGroupNew)
    {
        $this->indexerRegistry->get(Processor::INDEXER_ID)->invalidate();
    }
}
