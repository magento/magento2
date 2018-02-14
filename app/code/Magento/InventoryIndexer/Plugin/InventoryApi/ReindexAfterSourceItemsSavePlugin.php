<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventoryApi;

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
     * @var SourceItemIndexer
     */
    private $sourceItemIndexer;

    /**
     * @param GetSourceItemId $getSourceItemId
     * @param SourceItemIndexer $sourceItemIndexer
     */
    public function __construct(GetSourceItemId $getSourceItemId, SourceItemIndexer $sourceItemIndexer)
    {
        $this->getSourceItemId = $getSourceItemId;
        $this->sourceItemIndexer = $sourceItemIndexer;
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
            $this->sourceItemIndexer->executeList($sourceItemIds);
        }
    }
}
