<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\Source\GetPartialReindexData;
use Magento\Inventory\Indexer\StockItem\IndexDataProvider;

/**
 * @inheritdoc
 */
class Source implements SourceIndexerInterface
{
    /**
     * @var GetPartialReindexData
     */
    private $getPartialReindexData;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @var IndexDataProvider
     */
    private $indexDataProvider;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var StockItemIndexerInterface
     */
    private $stockItemIndexer;

    /**
     * @param GetPartialReindexData $getPartialReindexData
     * @param IndexStructureInterface $indexStructureHandler
     * @param IndexHandlerInterface $indexHandler
     * @param IndexDataProvider $indexDataProvider
     * @param IndexNameBuilder $indexNameBuilder
     * @param StockItemIndexerInterface $stockItemIndexer
     */
    public function __construct(
        GetPartialReindexData $getPartialReindexData,
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexDataProvider $indexDataProvider,
        IndexNameBuilder $indexNameBuilder,
        StockItemIndexerInterface $stockItemIndexer
    ) {
        $this->getPartialReindexData = $getPartialReindexData;
        $this->indexStructure = $indexStructureHandler;
        $this->indexHandler = $indexHandler;
        $this->indexDataProvider = $indexDataProvider;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->stockItemIndexer = $stockItemIndexer;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->stockItemIndexer->executeFull();
    }

    /**
     * @inheritdoc
     */
    public function executeRow($sourceId)
    {
        $this->executeList([$sourceId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $sourceIds)
    {
        $stockIds = $this->getPartialReindexData->execute($sourceIds);
        $this->stockItemIndexer->executeList($stockIds);
    }
}
