<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventoryApi;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventoryIndexer\Model\ResourceModel\IsInvalidationRequiredForSource;

/**
 * Invalidate Inventory Indexer after Source was enabled or disabled.
 */
class InvalidateAfterEnablingOrDisablingSourcePlugin
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var IsInvalidationRequiredForSource
     */
    private $isInvalidationRequiredForSource;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param IsInvalidationRequiredForSource $isInvalidationRequiredForSource
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        IsInvalidationRequiredForSource $isInvalidationRequiredForSource
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->isInvalidationRequiredForSource = $isInvalidationRequiredForSource;
    }

    /**
     * Invalidate Inventory Indexer after Source was enabled or disabled.
     *
     * @param SourceRepositoryInterface $subject
     * @param callable $proceed
     * @param SourceInterface $source
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        SourceRepositoryInterface $subject,
        callable $proceed,
        SourceInterface $source
    ) {
        $invalidationRequired = false;
        if ($source->getSourceCode()) {
            $invalidationRequired = $this->isInvalidationRequiredForSource->execute(
                $source->getSourceCode(),
                (bool)$source->isEnabled()
            );
        }

        $proceed($source);

        if ($invalidationRequired) {
            $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
            if ($indexer->isValid()) {
                $indexer->invalidate();
            }
        }
    }
}
