<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Category\Action;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Category rows indexer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rows extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Limitation by products
     *
     * @var int[]
     */
    protected $limitationByProducts;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var EventManagerInterface|null
     */
    private $eventManager;

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param QueryGenerator|null $queryGenerator
     * @param MetadataPool|null $metadataPool
     * @param CacheContext|null $cacheContext
     * @param EventManagerInterface|null $eventManager
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        Config $config,
        QueryGenerator $queryGenerator = null,
        MetadataPool $metadataPool = null,
        CacheContext $cacheContext = null,
        EventManagerInterface $eventManager = null
    ) {
        parent::__construct($resource, $storeManager, $config, $queryGenerator, $metadataPool);
        $this->cacheContext = $cacheContext ?: ObjectManager::getInstance()->get(CacheContext::class);
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(EventManagerInterface::class);
    }

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return $this
     * @throws \Exception if metadataPool doesn't contain metadata for ProductInterface
     * @throws \DomainException
     */
    public function execute(array $entityIds = [], $useTempTable = false)
    {
        $idsToBeReIndexed = $this->getProductIdsWithParents($entityIds);

        $this->limitationByProducts = $idsToBeReIndexed;
        $this->useTempTable = $useTempTable;

        $affectedCategories = $this->getCategoryIdsFromIndex($idsToBeReIndexed);

        $this->removeEntries();

        $this->reindex();

        $affectedCategories = array_merge($affectedCategories, $this->getCategoryIdsFromIndex($idsToBeReIndexed));

        $this->registerProducts($idsToBeReIndexed);
        $this->registerCategories($affectedCategories);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);

        return $this;
    }

    /**
     * Get IDs of parent products by their child IDs.
     *
     * Returns identifiers of parent product from the catalog_product_relation.
     * Please note that returned ids don't contain ids of passed child products.
     *
     * @param int[] $childProductIds
     * @return int[]
     * @throws \Exception if metadataPool doesn't contain metadata for ProductInterface
     * @throws \DomainException
     */
    private function getProductIdsWithParents(array $childProductIds): array
    {
        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $fieldForParent = $metadata->getLinkField();

        $select = $this->connection
            ->select()
            ->from(['relation' => $this->getTable('catalog_product_relation')], [])
            ->distinct(true)
            ->where('child_id IN (?)', $childProductIds)
            ->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'relation.parent_id = cpe.' . $fieldForParent,
                ['cpe.entity_id']
            );

        $parentProductIds = $this->connection->fetchCol($select);
        $ids = array_unique(array_merge($childProductIds, $parentProductIds));
        foreach ($ids as $key => $id) {
            $ids[$key] = (int) $id;
        }

        return $ids;
    }

    /**
     * Register affected products
     *
     * @param array $entityIds
     * @return void
     */
    private function registerProducts($entityIds)
    {
        $this->cacheContext->registerEntities(Product::CACHE_TAG, $entityIds);
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
     * Remove index entries before reindexation
     *
     * @return void
     */
    protected function removeEntries()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->connection->delete(
                $this->getIndexTable($store->getId()),
                ['product_id IN (?)' => $this->limitationByProducts]
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
        return $select->where('ccp.product_id IN (?)', $this->limitationByProducts);
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
        return $select->where('ccp.product_id IN (?)', $this->limitationByProducts);
    }

    /**
     * Get select for all products
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAllProducts(\Magento\Store\Model\Store $store)
    {
        $select = parent::getAllProducts($store);
        return $select->where('cp.entity_id IN (?)', $this->limitationByProducts);
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
     * Returns a list of category ids which are assigned to product ids in the index
     *
     * @param array $productIds
     * @return array
     */
    private function getCategoryIdsFromIndex(array $productIds): array
    {
        $categoryIds = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeCategories = $this->connection->fetchCol(
                $this->connection->select()
                    ->from($this->getIndexTable($store->getId()), ['category_id'])
                    ->where('product_id IN (?)', $productIds)
                    ->distinct()
            );
            $categoryIds[] = $storeCategories;
        }
        $categoryIds = array_merge(...$categoryIds);

        $parentCategories = [$categoryIds];
        foreach ($categoryIds as $categoryId) {
            $parentIds = explode('/', $this->getPathFromCategoryId($categoryId));
            $parentCategories[] = $parentIds;
        }
        $categoryIds = array_unique(array_merge(...$parentCategories));

        return $categoryIds;
    }
}
