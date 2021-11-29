<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Indexer\Model\WorkingStateProvider;

/**
 * Reindex multiple rows action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rows extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Limitation by categories
     *
     * @var int[]
     */
    protected $limitationByCategories;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var EventManagerInterface|null
     */
    private $eventManager;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var WorkingStateProvider
     */
    private $workingStateProvider;

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param QueryGenerator|null $queryGenerator
     * @param MetadataPool|null $metadataPool
     * @param TableMaintainer|null $tableMaintainer
     * @param CacheContext|null $cacheContext
     * @param EventManagerInterface|null $eventManager
     * @param IndexerRegistry|null $indexerRegistry
     * @param WorkingStateProvider|null $workingStateProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) Preserve compatibility with the parent class
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        Config $config,
        QueryGenerator $queryGenerator = null,
        MetadataPool $metadataPool = null,
        ?TableMaintainer $tableMaintainer = null,
        CacheContext $cacheContext = null,
        EventManagerInterface $eventManager = null,
        IndexerRegistry $indexerRegistry = null,
        ?WorkingStateProvider $workingStateProvider = null
    ) {
        parent::__construct($resource, $storeManager, $config, $queryGenerator, $metadataPool, $tableMaintainer);
        $this->cacheContext = $cacheContext ?: ObjectManager::getInstance()->get(CacheContext::class);
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(EventManagerInterface::class);
        $this->indexerRegistry = $indexerRegistry ?: ObjectManager::getInstance()->get(IndexerRegistry::class);
        $this->workingStateProvider = $workingStateProvider ?:
            ObjectManager::getInstance()->get(WorkingStateProvider::class);
    }

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(array $entityIds = [], $useTempTable = false)
    {
        foreach ($entityIds as $entityId) {
            $this->limitationByCategories[] = (int)$entityId;
            $path = $this->getPathFromCategoryId($entityId);
            if (!empty($path)) {
                $pathIds = explode('/', $path);
                foreach ($pathIds as $pathId) {
                    $this->limitationByCategories[] = (int)$pathId;
                }
            }
        }
        $this->limitationByCategories = array_unique($this->limitationByCategories);
        $this->useTempTable = $useTempTable;
        $indexer = $this->indexerRegistry->get(ProductCategoryIndexer::INDEXER_ID);
        $workingState = $this->isWorkingState();

        if (!$indexer->isScheduled()
            || ($indexer->isScheduled() && !$useTempTable)
            || ($indexer->isScheduled() && $useTempTable && !$workingState)) {
            if ($useTempTable && !$workingState && $indexer->isScheduled()) {
                foreach ($this->storeManager->getStores() as $store) {
                    $this->connection->truncateTable($this->getIndexTable($store->getId()));
                }
            } else {
                $this->removeEntries();
            }

            $this->reindex();

            // get actual state
            $workingState = $this->isWorkingState();

            if ($useTempTable && !$workingState && $indexer->isScheduled()) {
                foreach ($this->storeManager->getStores() as $store) {
                    $removalCategoryIds = array_diff($this->limitationByCategories, [$this->getRootCategoryId($store)]);
                    $this->connection->delete(
                        $this->tableMaintainer->getMainTable($store->getId()),
                        ['category_id IN (?)' => $removalCategoryIds]
                    );
                    $select = $this->connection->select()
                        ->from($this->tableMaintainer->getMainReplicaTable($store->getId()));
                    $this->connection->query(
                        $this->connection->insertFromSelect(
                            $select,
                            $this->tableMaintainer->getMainTable($store->getId()),
                            [],
                            AdapterInterface::INSERT_ON_DUPLICATE
                        )
                    );
                }
            }

            $this->registerCategories($entityIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }

        return $this;
    }

    /**
     * Get state for current and shared indexer
     *
     * @return bool
     */
    private function isWorkingState() : bool
    {
        $indexer = $this->indexerRegistry->get(ProductCategoryIndexer::INDEXER_ID);
        $sharedIndexer = $this->indexerRegistry->get(CategoryProductIndexer::INDEXER_ID);
        return $this->workingStateProvider->isWorking($indexer->getId())
            || $this->workingStateProvider->isWorking($sharedIndexer->getId());
    }

    /**
     * Register categories assigned to products
     *
     * @param array $categoryIds
     * @return void
     */
    private function registerCategories(array $categoryIds)
    {
        if ($categoryIds) {
            $this->cacheContext->registerEntities(Category::CACHE_TAG, $categoryIds);
        }
    }

    /**
     * Return array of all category root IDs + tree root ID
     *
     * @param \Magento\Store\Model\Store $store
     * @return int
     */
    private function getRootCategoryId($store)
    {
        $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        if ($this->getPathFromCategoryId($store->getRootCategoryId())) {
            $rootId = $store->getRootCategoryId();
        }
        return $rootId;
    }

    /**
     * Remove index entries before reindexation
     *
     * @return void
     */
    private function removeEntries()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $removalCategoryIds = array_diff($this->limitationByCategories, [$this->getRootCategoryId($store)]);
            $this->connection->delete(
                $this->getIndexTable($store->getId()),
                ['category_id IN (?)' => $removalCategoryIds]
            );
        }
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getNonAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        $select = parent::getNonAnchorCategoriesSelect($store);
        return $select->where('cc.entity_id IN (?)', $this->limitationByCategories);
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        $select = parent::getAnchorCategoriesSelect($store);
        return $select->where('cc.entity_id IN (?)', $this->limitationByCategories);
    }

    /**
     * Check whether select ranging is needed
     *
     * @return bool
     */
    protected function isRangingNeeded()
    {
        return false;
    }

    /**
     * Check whether indexation of root category is needed
     *
     * @return bool
     */
    protected function isIndexRootCategoryNeeded()
    {
        return false;
    }
}
