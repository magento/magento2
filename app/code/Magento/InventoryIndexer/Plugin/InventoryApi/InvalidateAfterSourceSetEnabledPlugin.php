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
use Magento\InventoryIndexer\Model\GetSourceEnabled;
use Magento\InventoryIndexer\Model\GetSourceLinked;

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
     * @var GetSourceEnabled
     */
    private $getSourceEnabled;

    /**
     * @var GetSourceLinked
     */
    private $getSourceLinked;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param GetSourceEnabled $getSourceEnabled
     * @param GetSourceLinked $getSourceLinked
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        GetSourceEnabled $getSourceEnabled,
        GetSourceLinked $getSourceLinked
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->getSourceEnabled = $getSourceEnabled;
        $this->getSourceLinked = $getSourceLinked;
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
        $oldStatus = (int)$this->getSourceEnabled->execute($sourceCode);
        $proceed($source);
        $status = (int)$source->isEnabled();
        if (($oldStatus !== $status) && $this->getSourceLinked->execute($sourceCode)) {
            $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
            if ($indexer->isValid()) {
                $indexer->invalidate();
            }
        }
    }
}
