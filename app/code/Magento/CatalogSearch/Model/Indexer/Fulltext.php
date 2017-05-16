<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\Indexer\Scope\State;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide functionality for Fulltext Search indexing.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Framework\Search\Request\DimensionFactory
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
     * @var IndexSwitcherInterface
     */
    private $indexSwitcher;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\State
     */
    private $indexScopeState;

    /**
     * Holder for MetadataPool instance.
     *
     * @var MetadataPool
     */
    private $metadataPool = null;

    /**
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param FulltextResource $fulltextResource
     * @param SearchRequestConfig $searchRequestConfig
     * @param array $data
     * @param IndexSwitcherInterface $indexSwitcher
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
        IndexSwitcherInterface $indexSwitcher = null,
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
            $indexSwitcher = ObjectManager::getInstance()->get(IndexSwitcherInterface::class);
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
            $productIds = array_unique(array_merge($ids, $this->getRelationsByChild($ids)));
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($productIds));
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
        }
    }

    /**
     * Retrieve product relations by children.
     *
     * @param int|array $childIds
     * @return array
     */
    private function getRelationsByChild($childIds)
    {
        $connection = $this->fulltextResource->getConnection();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['relation' => $this->fulltextResource->getTable('catalog_product_relation')],
            []
        )->join(
            ['cpe' => $this->fulltextResource->getTable('catalog_product_entity')],
            'cpe.' . $linkField . ' = relation.parent_id',
            ['cpe.entity_id']
        )->where(
            'relation.child_id IN (?)',
            $childIds
        )->distinct(true);

        return $connection->fetchCol($select);
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

    /**
     * Get Metadata Pool instance.
     *
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if ($this->metadataPool === null) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }

        return $this->metadataPool;
    }
}
