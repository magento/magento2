<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndex\Plugin\InventoryApi\SourceItemsDelete;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryIndex\Indexer\SourceItem\GetSourceItemId;
use Magento\InventoryIndex\Indexer\SourceItem\SourceItemIndexer;

/**
 * Reindex after source items delete plugin
 */
class ReindexAfterSourceItemsDeletePlugin
{
    /**
     * @var GetSourceItemId
     */
    private $getSourceItemId;

    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @param GetSourceItemId $getSourceItemId
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(GetSourceItemId $getSourceItemId, IndexerInterfaceFactory $indexerFactory)
    {
        $this->getSourceItemId = $getSourceItemId;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @param SourceItemsDeleteInterface $subject
     * @param callable $proceed
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        SourceItemsDeleteInterface $subject,
        callable $proceed,
        array $sourceItems
    ) {

        // TODO: replace on multi operation
        $sourceItemIds = array_map(
            function (SourceItemInterface $sourceItem) {
                return $this->getSourceItemId->execute($sourceItem->getSku(), $sourceItem->getSourceCode());
            },
            $sourceItems
        );

        $proceed($sourceItems);

        /** @var IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create();
        $indexer->load(SourceItemIndexer::INDEXER_ID);
        $indexer->reindexList($sourceItemIds);
    }
}
