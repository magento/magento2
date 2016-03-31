<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Action;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Rows extends \Magento\Catalog\Model\Indexer\Category\Flat\AbstractAction
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        parent::__construct($resource, $storeManager, $resourceHelper);
    }

    /**
     * Return index table name
     *
     * @param \Magento\Store\Model\Store $store
     * @param bool $useTempTable
     * @return string
     */
    protected function getTableNameByStore(\Magento\Store\Model\Store $store, $useTempTable)
    {
        $tableName = $this->getMainStoreTable($store->getId());
        return $useTempTable ? $this->addTemporaryTableSuffix($tableName) : $tableName;
    }

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return Rows
     */
    public function reindex(array $entityIds = [], $useTempTable = false)
    {
        $stores = $this->storeManager->getStores();

        /* @var $store \Magento\Store\Model\Store */
        foreach ($stores as $store) {
            $tableName = $this->getTableNameByStore($store, $useTempTable);

            if (!$this->connection->isTableExists($tableName)) {
                continue;
            }

            /** @TODO Do something with chunks */
            $categoriesIdsChunks = array_chunk($entityIds, 500);
            foreach ($categoriesIdsChunks as $categoriesIdsChunk) {
                $categoriesIdsChunk = $this->filterIdsByStore($categoriesIdsChunk, $store);

                $attributesData = $this->getAttributeValues($categoriesIdsChunk, $store->getId());
                $data = [];
                foreach ($categoriesIdsChunk as $categoryId) {
                    if (!isset($attributesData[$categoryId])) {
                        continue;
                    }

                    try {
                        $category = $this->categoryRepository->get($categoryId);
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }

                    $data[] = $this->prepareValuesToInsert(
                        array_merge(
                            $category->getData(),
                            $attributesData[$categoryId],
                            ['store_id' => $store->getId()]
                        )
                    );
                }

                foreach ($data as $row) {
                    $updateFields = [];
                    foreach (array_keys($row) as $key) {
                        $updateFields[$key] = $key;
                    }
                    $this->connection->insertOnDuplicate($tableName, $row, $updateFields);
                }
            }
            $this->deleteNonStoreCategories($store, $useTempTable);
        }

        return $this;
    }

    /**
     * Delete non stores categories
     *
     * @param \Magento\Store\Model\Store $store
     * @param bool $useTempTable
     * @return void
     */
    protected function deleteNonStoreCategories(\Magento\Store\Model\Store $store, $useTempTable)
    {
        $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;

        $rootIdExpr = $this->connection->quote((string)$rootId);
        $rootCatIdExpr = $this->connection->quote("{$rootId}/{$store->getRootCategoryId()}");
        $catIdExpr = $this->connection->quote("{$rootId}/{$store->getRootCategoryId()}/%");

        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->connection->select()->from(
            ['cf' => $this->getTableNameByStore($store, $useTempTable)]
        )->where(
            "cf.path = {$rootIdExpr} OR cf.path = {$rootCatIdExpr} OR cf.path like {$catIdExpr}"
        )->where(
            'cf.entity_id NOT IN (?)',
            new \Zend_Db_Expr(
                $this->connection->select()->from(
                    ['ce' => $this->getTableName('catalog_category_entity')],
                    ['entity_id']
                )
            )
        );

        $sql = $select->deleteFromSelect('cf');
        $this->connection->query($sql);
    }

    /**
     * Filter category ids by store
     *
     * @param int[] $ids
     * @param \Magento\Store\Model\Store $store
     * @return int[]
     */
    protected function filterIdsByStore(array $ids, $store)
    {
        $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;

        $rootIdExpr = $this->connection->quote((string)$rootId);
        $rootCatIdExpr = $this->connection->quote("{$rootId}/{$store->getRootCategoryId()}");
        $catIdExpr = $this->connection->quote("{$rootId}/{$store->getRootCategoryId()}/%");

        $select = $this->connection->select()->from(
            $this->getTableName('catalog_category_entity'),
            ['entity_id']
        )->where(
            "path = {$rootIdExpr} OR path = {$rootCatIdExpr} OR path like {$catIdExpr}"
        )->where(
            "entity_id IN (?)",
            $ids
        );

        $resultIds = [];
        foreach ($this->connection->fetchAll($select) as $category) {
            $resultIds[] = $category['entity_id'];
        }
        return $resultIds;
    }
}
