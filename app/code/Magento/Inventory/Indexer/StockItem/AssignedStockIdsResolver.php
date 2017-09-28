<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mview\ViewInterface;
use Magento\Framework\Mview\ViewInterfaceFactory;
use Magento\Inventory\Indexer\StockItemIndexerInterface;

/**
 * Decorator for returns all assigned stock ids by given sources ids or existing in mview changelog.
 *
 * In case for full reindex we need to update all stock index tables regarding mview changelog data.
 */
class AssignedStockIdsResolver
{
    /**
     * @var GetAssignedStockIds
     */
    private $getAssignedStockIds;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var ViewInterfaceFactory
     */
    private $viewFactory;

    /**
     * @param GetAssignedStockIds $getAssignedStockIds
     * @param IndexerRegistry $indexerRegistry
     * @param ViewInterfaceFactory $viewFactory
     */
    public function __construct(
        GetAssignedStockIds $getAssignedStockIds,
        IndexerRegistry $indexerRegistry,
        ViewInterfaceFactory $viewFactory
    ) {
        $this->getAssignedStockIds = $getAssignedStockIds;
        $this->indexerRegistry = $indexerRegistry;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Returns all assigned stock ids by given sources ids or existing in mview changelog.
     *
     * @param int[] $sourceIds
     * @return int[] List of stock ids
     */
    public function execute(array $sourceIds = []): array
    {
        if (empty($sourceIds)) {
            /** @var ViewInterface $view */
            $view = $this->viewFactory->create();
            $view->load(StockItemIndexerInterface::MVIEW_ID);
            $currentVersionId = $view->getChangelog()->getVersion();
            /** TODO: better refactor to query without condition */
            $stockIds = $view->getChangelog()->getList(0, $currentVersionId);
            $view->getChangelog()->clear($currentVersionId);
        } else {
            $stockIds = $this->getAssignedStockIds->execute($sourceIds);
        }

        return $stockIds;
    }
}
