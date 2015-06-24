<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;
use Magento\CatalogSearch\Model\Indexer\IndexerHandler;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Fulltext implements \Magento\Indexer\Model\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalogsearch_fulltext';

    /** @var array index structure */
    protected $data;

    /**
     * @var IndexerHandler
     */
    private $indexerHandlerFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;
    /**
     * @var Full
     */
    private $fullAction;

    /**
     * @param Full $fullAction
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Full $fullAction,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        array $data
    ) {
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->data = $data;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->fullAction = $fullAction;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $ids = ($ids !== null) ? new \ArrayObject($ids) : $ids;
        $storeIds = array_keys($this->storeManager->getStores());
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->deleteIndex([$dimension], $ids);
            $saveHandler->saveIndex(
                [$dimension],
                $this->fullAction->rebuildStoreIndex($storeId, $ids)
            );
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->cleanIndex([$dimension]);
            $saveHandler->saveIndex(
                [$dimension],
                $this->fullAction->rebuildStoreIndex($storeId)
            );
        }
       // $this->fulltextResource->resetSearchResults();
       // $this->searchRequestConfig->reset();

    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
