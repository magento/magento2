<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Indexer\Category\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

/**
 * Class AbstractAction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractAction
{
    /**
     * Chunk size
     */
    const RANGE_CATEGORY_STEP = 500;

    /**
     * Chunk size for product
     */
    const RANGE_PRODUCT_STEP = 1000000;

    /**
     * Catalog category index table name
     */
    const MAIN_INDEX_TABLE = 'catalog_category_product_index';

    /**
     * Suffix for table to show it is temporary
     */
    const TEMPORARY_TABLE_SUFFIX = '_tmp';

    /**
     * Cached non anchor categories select by store id
     *
     * @var Select[]
     */
    protected $nonAnchorSelects = [];

    /**
     * Cached anchor categories select by store id
     *
     * @var Select[]
     */
    protected $anchorSelects = [];

    /**
     * Cached all product select by store id
     *
     * @var Select[]
     */
    protected $productsSelects = [];

    /**
     * Category path by id
     *
     * @var string[]
     */
    protected $categoryPath = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $config;

    /**
     * Whether to use main or temporary index table
     *
     * @var bool
     */
    protected $useTempTable = true;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var string
     */
    protected $tempTreeIndexTableName;

    /**
     * @var QueryGenerator
     */
    private $queryGenerator;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $config
     * @param QueryGenerator $queryGenerator
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $config,
        QueryGenerator $queryGenerator = null,
        MetadataPool $metadataPool = null
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->queryGenerator = $queryGenerator ?: ObjectManager::getInstance()->get(QueryGenerator::class);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * Run full reindex
     *
     * @return $this
     */
    abstract public function execute();

    /**
     * Run reindexation
     *
     * @return void
     */
    protected function reindex()
    {
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->getPathFromCategoryId($store->getRootCategoryId())) {
                $this->reindexRootCategory($store);
                $this->reindexAnchorCategories($store);
                $this->reindexNonAnchorCategories($store);
            }
        }
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * Return main index table name
     *
     * @return string
     */
    protected function getMainTable()
    {
        return $this->getTable(self::MAIN_INDEX_TABLE);
    }

    /**
     * Return temporary index table name
     *
     * @return string
     */
    protected function getMainTmpTable()
    {
        return $this->useTempTable
            ? $this->getTable(self::MAIN_INDEX_TABLE . self::TEMPORARY_TABLE_SUFFIX)
            : $this->getMainTable();
    }

    /**
     * Return category path by id
     *
     * @param int $categoryId
     * @return string
     */
    protected function getPathFromCategoryId($categoryId)
    {
        if (!isset($this->categoryPath[$categoryId])) {
            $this->categoryPath[$categoryId] = $this->connection->fetchOne(
                $this->connection->select()->from(
                    $this->getTable('catalog_category_entity'),
                    ['path']
                )->where(
                    'entity_id = ?',
                    $categoryId
                )
            );
        }
        return $this->categoryPath[$categoryId];
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param Store $store
     * @return Select
     * @throws \Exception when metadata not found for ProductInterface
     */
    protected function getNonAnchorCategoriesSelect(Store $store)
    {
        if (!isset($this->nonAnchorSelects[$store->getId()])) {
            $statusAttributeId = $this->config->getAttribute(
                Product::ENTITY,
                'status'
            )->getId();
            $visibilityAttributeId = $this->config->getAttribute(
                Product::ENTITY,
                'visibility'
            )->getId();

            $rootPath = $this->getPathFromCategoryId($store->getRootCategoryId());

            $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
            $linkField = $metadata->getLinkField();
            $select = $this->connection->select()->from(
                ['cc' => $this->getTable('catalog_category_entity')],
                []
            )->joinInner(
                ['ccp' => $this->getTable('catalog_category_product')],
                'ccp.category_id = cc.entity_id',
                []
            )->joinInner(
                ['cpw' => $this->getTable('catalog_product_website')],
                'cpw.product_id = ccp.product_id',
                []
            )->joinInner(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'ccp.product_id = cpe.entity_id',
                []
            )->joinInner(
                ['cpsd' => $this->getTable('catalog_product_entity_int')],
                'cpsd.' . $linkField . ' = cpe.' . $linkField . ' AND cpsd.store_id = 0' .
                ' AND cpsd.attribute_id = ' .
                $statusAttributeId,
                []
            )->joinLeft(
                ['cpss' => $this->getTable('catalog_product_entity_int')],
                'cpss.' . $linkField . ' = cpe.' . $linkField . ' AND cpss.attribute_id = cpsd.attribute_id' .
                ' AND cpss.store_id = ' .
                $store->getId(),
                []
            )->joinInner(
                ['cpvd' => $this->getTable('catalog_product_entity_int')],
                'cpvd.' . $linkField . ' = cpe.' . $linkField . ' AND cpvd.store_id = 0' .
                ' AND cpvd.attribute_id = ' .
                $visibilityAttributeId,
                []
            )->joinLeft(
                ['cpvs' => $this->getTable('catalog_product_entity_int')],
                'cpvs.' . $linkField . ' = cpe.' . $linkField . ' AND cpvs.attribute_id = cpvd.attribute_id' .
                ' AND cpvs.store_id = ' .
                $store->getId(),
                []
            )->where(
                'cc.path LIKE ' . $this->connection->quote($rootPath . '/%')
            )->where(
                'cpw.website_id = ?',
                $store->getWebsiteId()
            )->where(
                $this->connection->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            )->where(
                $this->connection->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
                [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                ]
            )->columns(
                [
                    'category_id' => 'cc.entity_id',
                    'product_id' => 'ccp.product_id',
                    'position' => 'ccp.position',
                    'is_parent' => new \Zend_Db_Expr('1'),
                    'store_id' => new \Zend_Db_Expr($store->getId()),
                    'visibility' => new \Zend_Db_Expr(
                        $this->connection->getIfNullSql('cpvs.value', 'cpvd.value')
                    ),
                ]
            );

            $this->addFilteringByChildProductsToSelect($select, $store);

            $this->nonAnchorSelects[$store->getId()] = $select;
        }

        return $this->nonAnchorSelects[$store->getId()];
    }

    /**
     * Add filtering by child products to select
     *
     * It's used for correct handling of composite products.
     * This method makes assumption that select already joins `catalog_product_entity` as `cpe`.
     *
     * @param Select $select
     * @param Store $store
     * @return void
     * @throws \Exception when metadata not found for ProductInterface
     */
    private function addFilteringByChildProductsToSelect(Select $select, Store $store)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $statusAttributeId = $this->config->getAttribute(Product::ENTITY, 'status')->getId();

        $select->joinLeft(
            ['relation' => $this->getTable('catalog_product_relation')],
            'cpe.' . $linkField . ' = relation.parent_id',
            []
        )->joinLeft(
            ['relation_product_entity' => $this->getTable('catalog_product_entity')],
            'relation.child_id = relation_product_entity.entity_id',
            []
        )->joinLeft(
            ['child_cpsd' => $this->getTable('catalog_product_entity_int')],
            'child_cpsd.' . $linkField . ' = '. 'relation_product_entity.' . $linkField
            . ' AND child_cpsd.store_id = 0'
            . ' AND child_cpsd.attribute_id = ' . $statusAttributeId,
            []
        )->joinLeft(
            ['child_cpss' => $this->getTable('catalog_product_entity_int')],
            'child_cpss.' . $linkField . ' = '. 'relation_product_entity.' . $linkField . ''
            . ' AND child_cpss.attribute_id = child_cpsd.attribute_id'
            . ' AND child_cpss.store_id = ' . $store->getId(),
            []
        )->where(
            'relation.child_id IS NULL OR '
            . $this->connection->getIfNullSql('child_cpss.value', 'child_cpsd.value') . ' = ?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        )->group(
            [
                'cc.entity_id',
                'ccp.product_id',
                'visibility'
            ]
        );
    }

    /**
     * Check whether select ranging is needed
     *
     * @return bool
     */
    protected function isRangingNeeded()
    {
        return true;
    }

    /**
     * Return selects cut by min and max.
     *
     * @param Select $select
     * @param string $field
     * @param int $range
     * @return Select[]
     */
    protected function prepareSelectsByRange(Select $select, $field, $range = self::RANGE_CATEGORY_STEP)
    {
        if ($this->isRangingNeeded()) {
            $iterator = $this->queryGenerator->generate(
                $field,
                $select,
                $range,
                \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
            );

            $queries = [];
            foreach ($iterator as $query) {
                $queries[] = $query;
            }

            return $queries;
        }

        return [$select];
    }

    /**
     * Reindex products of non anchor categories
     *
     * @param Store $store
     * @return void
     */
    protected function reindexNonAnchorCategories(Store $store)
    {
        $selects = $this->prepareSelectsByRange($this->getNonAnchorCategoriesSelect($store), 'entity_id');
        foreach ($selects as $select) {
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $select,
                    $this->getMainTmpTable(),
                    ['category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }

    /**
     * Check if anchor select isset
     *
     * @param Store $store
     * @return bool
     */
    protected function hasAnchorSelect(Store $store)
    {
        return isset($this->anchorSelects[$store->getId()]);
    }

    /**
     * Create anchor select
     *
     * @param Store $store
     * @return Select
     * @throws \Exception when metadata not found for ProductInterface or CategoryInterface
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function createAnchorSelect(Store $store)
    {
        $isAnchorAttributeId = $this->config->getAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'is_anchor'
        )->getId();
        $statusAttributeId = $this->config->getAttribute(Product::ENTITY, 'status')->getId();
        $visibilityAttributeId = $this->config->getAttribute(
            Product::ENTITY,
            'visibility'
        )->getId();
        $rootCatIds = explode('/', $this->getPathFromCategoryId($store->getRootCategoryId()));
        array_pop($rootCatIds);

        $temporaryTreeTable = $this->makeTempCategoryTreeIndex();

        $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $categoryMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\CategoryInterface::class);
        $productLinkField = $productMetadata->getLinkField();
        $categoryLinkField = $categoryMetadata->getLinkField();

        $select = $this->connection->select()->from(
            ['cc' => $this->getTable('catalog_category_entity')],
            []
        )->joinInner(
            ['cc2' => $temporaryTreeTable],
            'cc2.parent_id = cc.entity_id AND cc.entity_id NOT IN (' . implode(
                ',',
                $rootCatIds
            ) . ')',
            []
        )->joinInner(
            ['ccp' => $this->getTable('catalog_category_product')],
            'ccp.category_id = cc2.child_id',
            []
        )->joinInner(
            ['cpe' => $this->getTable('catalog_product_entity')],
            'ccp.product_id = cpe.entity_id',
            []
        )->joinInner(
            ['cpw' => $this->getTable('catalog_product_website')],
            'cpw.product_id = ccp.product_id',
            []
        )->joinInner(
            ['cpsd' => $this->getTable('catalog_product_entity_int')],
            'cpsd.' . $productLinkField . ' = cpe.' . $productLinkField . ' AND cpsd.store_id = 0'
                . ' AND cpsd.attribute_id = ' . $statusAttributeId,
            []
        )->joinLeft(
            ['cpss' => $this->getTable('catalog_product_entity_int')],
            'cpss.' . $productLinkField . ' = cpe.' . $productLinkField . ' AND cpss.attribute_id = cpsd.attribute_id' .
            ' AND cpss.store_id = ' .
            $store->getId(),
            []
        )->joinInner(
            ['cpvd' => $this->getTable('catalog_product_entity_int')],
            'cpvd.' . $productLinkField . ' = cpe. ' . $productLinkField . ' AND cpvd.store_id = 0' .
            ' AND cpvd.attribute_id = ' .
            $visibilityAttributeId,
            []
        )->joinLeft(
            ['cpvs' => $this->getTable('catalog_product_entity_int')],
            'cpvs.' . $productLinkField . ' = cpe.' . $productLinkField .
            ' AND cpvs.attribute_id = cpvd.attribute_id ' . 'AND cpvs.store_id = ' .
            $store->getId(),
            []
        )->joinInner(
            ['ccad' => $this->getTable('catalog_category_entity_int')],
            'ccad.' . $categoryLinkField . ' = cc.' . $categoryLinkField . ' AND ccad.store_id = 0' .
            ' AND ccad.attribute_id = ' . $isAnchorAttributeId,
            []
        )->joinLeft(
            ['ccas' => $this->getTable('catalog_category_entity_int')],
            'ccas.' . $categoryLinkField . ' = cc.' . $categoryLinkField
            . ' AND ccas.attribute_id = ccad.attribute_id AND ccas.store_id = ' .
            $store->getId(),
            []
        )->where(
            'cpw.website_id = ?',
            $store->getWebsiteId()
        )->where(
            $this->connection->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        )->where(
            $this->connection->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
            [
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
            ]
        )->where(
            $this->connection->getIfNullSql('ccas.value', 'ccad.value') . ' = ?',
            1
        )->columns(
            [
                'category_id' => 'cc.entity_id',
                'product_id' => 'ccp.product_id',
                'position' => new \Zend_Db_Expr('ccp.position + 10000'),
                'is_parent' => new \Zend_Db_Expr('0'),
                'store_id' => new \Zend_Db_Expr($store->getId()),
                'visibility' => new \Zend_Db_Expr($this->connection->getIfNullSql('cpvs.value', 'cpvd.value')),
            ]
        );

        $this->addFilteringByChildProductsToSelect($select, $store);

        return $select;
    }

    /**
     * Get temporary table name for concurrent indexing in persistent connection
     * Temp table name is NOT shared between action instances and each action has it's own temp tree index
     *
     * @return string
     */
    protected function getTemporaryTreeIndexTableName()
    {
        if (empty($this->tempTreeIndexTableName)) {
            $this->tempTreeIndexTableName = $this->connection->getTableName('temp_catalog_category_tree_index')
                . '_'
                . substr(md5(time() . rand(0, 999999999)), 0, 8);
        }

        return $this->tempTreeIndexTableName;
    }

    /**
     * Build and populate the temporary category tree index table
     *
     * Returns the name of the temporary table to use in queries.
     *
     * @return string
     */
    protected function makeTempCategoryTreeIndex()
    {
        // Note: this temporary table is per-connection, so won't conflict by prefix.
        $temporaryName = $this->getTemporaryTreeIndexTableName();

        $temporaryTable = $this->connection->newTable($temporaryName);
        $temporaryTable->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true]
        );
        $temporaryTable->addColumn(
            'child_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true]
        );
        // Each entry will be unique.
        $temporaryTable->addIndex(
            'idx_primary',
            ['parent_id', 'child_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY]
        );

        // Drop the temporary table in case it already exists on this (persistent?) connection.
        $this->connection->dropTemporaryTable($temporaryName);
        $this->connection->createTemporaryTable($temporaryTable);

        $this->fillTempCategoryTreeIndex($temporaryName);

        return $temporaryName;
    }

    /**
     * Populate the temporary category tree index table
     *
     * @param string $temporaryName
     */
    protected function fillTempCategoryTreeIndex($temporaryName)
    {
        // This finds all children (cc2) that descend from a parent (cc) by path.
        // For example, cc.path may be '1/2', and cc2.path may be '1/2/3/4/5'.
        $temporarySelect = $this->connection->select()->from(
            ['cc' => $this->getTable('catalog_category_entity')],
            ['parent_id' => 'entity_id']
        )->joinInner(
            ['cc2' => $this->getTable('catalog_category_entity')],
            'cc2.path LIKE ' . $this->connection->getConcatSql(
                [$this->connection->quoteIdentifier('cc.path'), $this->connection->quote('/%')]
            ),
            ['child_id' => 'entity_id']
        );

        $this->connection->query(
            $temporarySelect->insertFromSelect(
                $temporaryName,
                ['parent_id', 'child_id']
            )
        );
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param Store $store
     * @return Select
     */
    protected function getAnchorCategoriesSelect(Store $store)
    {
        if (!$this->hasAnchorSelect($store)) {
            $this->anchorSelects[$store->getId()] = $this->createAnchorSelect($store);
        }
        return $this->anchorSelects[$store->getId()];
    }

    /**
     * Reindex products of anchor categories
     *
     * @param Store $store
     * @return void
     */
    protected function reindexAnchorCategories(Store $store)
    {
        $selects = $this->prepareSelectsByRange($this->getAnchorCategoriesSelect($store), 'entity_id');

        foreach ($selects as $select) {
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $select,
                    $this->getMainTmpTable(),
                    ['category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }

    /**
     * Get select for all products
     *
     * @param Store $store
     * @return Select
     * @throws \Exception when metadata not found for ProductInterface
     */
    protected function getAllProducts(Store $store)
    {
        if (!isset($this->productsSelects[$store->getId()])) {
            $statusAttributeId = $this->config->getAttribute(
                Product::ENTITY,
                'status'
            )->getId();
            $visibilityAttributeId = $this->config->getAttribute(
                Product::ENTITY,
                'visibility'
            )->getId();

            $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
            $linkField = $metadata->getLinkField();

            $select = $this->connection->select()->from(
                ['cp' => $this->getTable('catalog_product_entity')],
                []
            )->joinInner(
                ['cpw' => $this->getTable('catalog_product_website')],
                'cpw.product_id = cp.entity_id',
                []
            )->joinInner(
                ['cpsd' => $this->getTable('catalog_product_entity_int')],
                'cpsd.' . $linkField . ' = cp.' . $linkField . ' AND cpsd.store_id = 0' .
                ' AND cpsd.attribute_id = ' .
                $statusAttributeId,
                []
            )->joinLeft(
                ['cpss' => $this->getTable('catalog_product_entity_int')],
                'cpss.' . $linkField . ' = cp.' . $linkField . ' AND cpss.attribute_id = cpsd.attribute_id' .
                ' AND cpss.store_id = ' .
                $store->getId(),
                []
            )->joinInner(
                ['cpvd' => $this->getTable('catalog_product_entity_int')],
                'cpvd.' . $linkField . ' = cp.' . $linkField . ' AND cpvd.store_id = 0' .
                ' AND cpvd.attribute_id = ' .
                $visibilityAttributeId,
                []
            )->joinLeft(
                ['cpvs' => $this->getTable('catalog_product_entity_int')],
                'cpvs.' . $linkField . ' = cp.' . $linkField . ' AND cpvs.attribute_id = cpvd.attribute_id ' .
                ' AND cpvs.store_id = ' .
                $store->getId(),
                []
            )->joinLeft(
                ['ccp' => $this->getTable('catalog_category_product')],
                'ccp.product_id = cp.entity_id',
                []
            )->where(
                'cpw.website_id = ?',
                $store->getWebsiteId()
            )->where(
                $this->connection->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            )->where(
                $this->connection->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
                [
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                ]
            )->group(
                'cp.entity_id'
            )->columns(
                [
                    'category_id' => new \Zend_Db_Expr($store->getRootCategoryId()),
                    'product_id' => 'cp.entity_id',
                    'position' => new \Zend_Db_Expr(
                        $this->connection->getCheckSql('ccp.product_id IS NOT NULL', 'ccp.position', '0')
                    ),
                    'is_parent' => new \Zend_Db_Expr(
                        $this->connection->getCheckSql('ccp.product_id IS NOT NULL', '1', '0')
                    ),
                    'store_id' => new \Zend_Db_Expr($store->getId()),
                    'visibility' => new \Zend_Db_Expr(
                        $this->connection->getIfNullSql('cpvs.value', 'cpvd.value')
                    ),
                ]
            );

            $this->productsSelects[$store->getId()] = $select;
        }

        return $this->productsSelects[$store->getId()];
    }

    /**
     * Check whether indexation of root category is needed
     *
     * @return bool
     */
    protected function isIndexRootCategoryNeeded()
    {
        return true;
    }

    /**
     * Reindex all products to root category
     *
     * @param Store $store
     * @return void
     */
    protected function reindexRootCategory(Store $store)
    {
        if ($this->isIndexRootCategoryNeeded()) {
            $selects = $this->prepareSelectsByRange(
                $this->getAllProducts($store),
                'entity_id',
                self::RANGE_PRODUCT_STEP
            );

            foreach ($selects as $select) {
                $this->connection->query(
                    $this->connection->insertFromSelect(
                        $select,
                        $this->getMainTmpTable(),
                        ['category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                    )
                );
            }
        }
    }
}
