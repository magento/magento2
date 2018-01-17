<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventoryApi\SourceItemsSave;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\GetSourceItemId;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

/**
 * Reindex after source items save plugin
 */
class ReindexAfterSourceItemsSavePlugin
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
     * @param SourceItemsSaveInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        SourceItemsSaveInterface $subject,
        $result,
        array $sourceItems
    ) {

        $sourceItemIds = [];
        foreach ($sourceItems as $sourceItem) {
            // TODO: replace on multi operation
            $sourceItemIds[] = $this->getSourceItemId->execute($sourceItem->getSku(), $sourceItem->getSourceCode());
        }

        if (count($sourceItemIds)) {
            /** @var IndexerInterface $indexer */
            $indexer = $this->indexerFactory->create();
            $indexer->load(SourceItemIndexer::INDEXER_ID);
            $indexer->reindexList($sourceItemIds);
        }
    }
}
