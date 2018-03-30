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
use Magento\InventoryIndexer\Model\GetInvalidationRequired;

/**
 * Invalidate Inventory Indexer after Source was enabled or disabled.
 */
class InvalidateAfterSourceSetEnabledPlugin
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var GetInvalidationRequired
     */
    private $getInvalidationRequired;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param GetInvalidationRequired $getInvalidationRequired
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        GetInvalidationRequired $getInvalidationRequired
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->getInvalidationRequired = $getInvalidationRequired;
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
        $sourceCode = $source->getSourceCode();
        $enabled = (int)$source->isEnabled();
        $invalidationRequired = $this->getInvalidationRequired->execute($sourceCode, $enabled);
        $proceed($source);
        if ($invalidationRequired) {
            $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
            if ($indexer->isValid()) {
                $indexer->invalidate();
            }
        }
    }
}
