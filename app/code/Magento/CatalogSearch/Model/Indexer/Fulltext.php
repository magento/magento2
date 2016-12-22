<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher;
use Magento\CatalogSearch\Model\Indexer\Scope\State;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide functionality for Fulltext Search indexing
 */
class Fulltext implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalogsearch_fulltext';

    /**
     * @var array index structure
     */
    protected $data;

    /**
     * @var IndexerHandlerFactory
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
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
     */
    private $fullAction;

    /**
     * @var FulltextResource
     */
    private $fulltextResource;

    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $searchRequestConfig;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\IndexSwitcher
     */
    private $indexSwitcher;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\State
     */
    private $indexScopeState;

    /**
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param FulltextResource $fulltextResource
     * @param SearchRequestConfig $searchRequestConfig
     * @param array $data
     * @param IndexSwitcher $indexSwitcher
     * @param Scope\State $indexScopeState
     */
    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        FulltextResource $fulltextResource,
        SearchRequestConfig $searchRequestConfig,
        array $data,
        IndexSwitcher $indexSwitcher = null,
        State $indexScopeState = null
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->fulltextResource = $fulltextResource;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
        if (null === $indexSwitcher) {
            $indexSwitcher = ObjectManager::getInstance()->get(IndexSwitcher::class);
        }
        if (null === $indexScopeState) {
            $indexScopeState = ObjectManager::getInstance()->get(State::class);
        }
        $this->indexSwitcher = $indexSwitcher;
        $this->indexScopeState = $indexScopeState;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($ids));
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
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
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        foreach ($storeIds as $storeId) {
            $dimensions = [$this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId])];
            $this->indexScopeState->useTemporaryIndex();

            $saveHandler->cleanIndex($dimensions);
            $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId));

            $this->indexSwitcher->switchIndex($dimensions);
            $this->indexScopeState->useRegularIndex();
        }
        $this->fulltextResource->resetSearchResults();
        $this->searchRequestConfig->reset();
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
