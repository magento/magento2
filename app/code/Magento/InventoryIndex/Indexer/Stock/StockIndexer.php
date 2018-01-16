<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndex\Indexer\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\MultiDimensionalIndex\Alias;
use Magento\Framework\MultiDimensionalIndex\IndexHandlerInterface;
use Magento\Framework\MultiDimensionalIndex\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndex\IndexStructureInterface;
use Magento\Framework\MultiDimensionalIndex\IndexTableSwitcherInterface;

/**
 * Stock indexer
 * Extension point for indexation
 *
 * @api
 */
class StockIndexer implements ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_stock';

    /**
     * @var GetAllAssignedStockIds
     */
    private $getAllAssignedStockIds;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexDataProviderByStockId
     */
    private $indexDataProviderByStockId;

    /**
     * @var IndexTableSwitcherInterface
     */
    private $indexTableSwitcher;

    /**
     * $indexStructure is reserved name for construct variable in index internal mechanism
     *
     * @param GetAllAssignedStockIds $getAllAssignedStockIds
     * @param IndexStructureInterface $indexStructureHandler
     * @param IndexHandlerInterface $indexHandler
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexDataProviderByStockId $indexDataProviderByStockId
     * @param IndexTableSwitcherInterface $indexTableSwitcher
     */
    public function __construct(
        GetAllAssignedStockIds $getAllAssignedStockIds,
        IndexStructureInterface $indexStructureHandler,
        IndexHandlerInterface $indexHandler,
        IndexNameBuilder $indexNameBuilder,
        IndexDataProviderByStockId $indexDataProviderByStockId,
        IndexTableSwitcherInterface $indexTableSwitcher
    ) {
        $this->getAllAssignedStockIds = $getAllAssignedStockIds;
        $this->indexStructure = $indexStructureHandler;
        $this->indexHandler = $indexHandler;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexDataProviderByStockId = $indexDataProviderByStockId;
        $this->indexTableSwitcher = $indexTableSwitcher;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $stockIds = $this->getAllAssignedStockIds->execute();
        $this->executeList($stockIds);
    }

    /**
     * @inheritdoc
     */
    public function executeRow($stockId)
    {
        $this->executeList([$stockId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $stockIds)
    {
        foreach ($stockIds as $stockId) {
            $replicaIndexName = $this->indexNameBuilder
                ->setIndexId('inventory_stock')
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_REPLICA)
                ->build();

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId('inventory_stock')
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexStructure->delete($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexStructure->create($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);

            if (!$this->indexStructure->isExist($mainIndexName, ResourceConnection::DEFAULT_CONNECTION)) {
                $this->indexStructure->create($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            }

            $this->indexHandler->saveIndex(
                $replicaIndexName,
                $this->indexDataProviderByStockId->execute((int)$stockId),
                ResourceConnection::DEFAULT_CONNECTION
            );
            $this->indexTableSwitcher->switch($mainIndexName, ResourceConnection::DEFAULT_CONNECTION);
            $this->indexStructure->delete($replicaIndexName, ResourceConnection::DEFAULT_CONNECTION);
        }
    }
}
