<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Framework\Data\Tree\Dbp;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Tree extends Dbp
{
    const ID_FIELD = 'id';

    const PATH_FIELD = 'path';

    const ORDER_FIELD = 'order';

    const LEVEL_FIELD = 'level';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    private $_eventManager;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     * @since 2.0.0
     */
    private $_attributeConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection\Factory
     * @since 2.0.0
     */
    private $_collectionFactory;

    /**
     * Categories resource collection
     *
     * @var Collection
     * @since 2.0.0
     */
    protected $_collection;

    /**
     * Join URL rewrites data to collection flag
     *
     * @var boolean
     * @since 2.0.0
     */
    protected $_joinUrlRewriteIntoCollection = false;

    /**
     * Inactive categories ids
     *
     * @var array
     * @since 2.0.0
     */
    protected $_inactiveCategoryIds = null;

    /**
     * Store id
     *
     * @var integer
     * @since 2.0.0
     */
    protected $_storeId = null;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $_coreResource;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Cache
     *
     * @var \Magento\Framework\App\CacheInterface
     * @since 2.0.0
     */
    protected $_cache;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category
     * @since 2.0.0
     */
    protected $_catalogCategory;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * Tree constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Category $catalogCategory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     * @param Collection\Factory $collectionFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category $catalogCategory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Attribute\Config $attributeConfig,
        \Magento\Catalog\Model\ResourceModel\Category\Collection\Factory $collectionFactory
    ) {
        $this->_catalogCategory = $catalogCategory;
        $this->_cache = $cache;
        $this->_storeManager = $storeManager;
        $this->_coreResource = $resource;
        parent::__construct(
            $resource->getConnection('catalog'),
            $resource->getTableName('catalog_category_entity'),
            [
                Dbp::ID_FIELD => 'entity_id',
                Dbp::PATH_FIELD => 'path',
                Dbp::ORDER_FIELD => 'position',
                Dbp::LEVEL_FIELD => 'level'
            ]
        );
        $this->_eventManager = $eventManager;
        $this->_attributeConfig = $attributeConfig;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     * @since 2.0.0
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Add data to collection
     *
     * @param Collection $collection
     * @param boolean $sorted
     * @param array $exclude
     * @param boolean $toLoad
     * @param boolean $onlyActive
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function addCollectionData(
        $collection = null,
        $sorted = false,
        $exclude = [],
        $toLoad = true,
        $onlyActive = false
    ) {
        if ($collection === null) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }

        $nodeIds = [];
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {
            $disabledIds = $this->_getDisabledIds($collection, $nodeIds);
            if ($disabledIds) {
                $collection->addFieldToFilter('entity_id', ['nin' => $disabledIds]);
            }
            $collection->addAttributeToFilter('is_active', 1);
            $collection->addAttributeToFilter('include_in_menu', 1);
        }

        if ($this->_joinUrlRewriteIntoCollection) {
            $collection->joinUrlRewrite();
            $this->_joinUrlRewriteIntoCollection = false;
        }

        if ($toLoad) {
            $collection->load();

            foreach ($collection as $category) {
                if ($this->getNodeById($category->getId())) {
                    $this->getNodeById($category->getId())->addData($category->getData());
                }
            }

            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }

        return $this;
    }

    /**
     * Add inactive categories ids
     *
     * @param mixed $ids
     * @return $this
     * @since 2.0.0
     */
    public function addInactiveCategoryIds($ids)
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }
        $this->_inactiveCategoryIds = array_merge($ids, $this->_inactiveCategoryIds);
        return $this;
    }

    /**
     * Retrieve inactive categories ids
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initInactiveCategoryIds()
    {
        $this->_inactiveCategoryIds = [];
        $this->_eventManager->dispatch('catalog_category_tree_init_inactive_category_ids', ['tree' => $this]);
        return $this;
    }

    /**
     * Retrieve inactive categories ids
     *
     * @return array
     * @since 2.0.0
     */
    public function getInactiveCategoryIds()
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }

        return $this->_inactiveCategoryIds;
    }

    /**
     * Return disable category ids
     *
     * @param Collection $collection
     * @param array $allIds
     * @return array
     * @since 2.0.0
     */
    protected function _getDisabledIds($collection, $allIds)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_inactiveItems = $this->getInactiveCategoryIds();
        $this->_inactiveItems = array_merge($this->_getInactiveItemIds($collection, $storeId), $this->_inactiveItems);

        $disabledIds = [];

        foreach ($allIds as $id) {
            $parents = $this->getNodeById($id)->getPath();
            foreach ($parents as $parent) {
                if (!$this->_getItemIsActive($parent->getId(), $storeId)) {
                    $disabledIds[] = $id;
                    continue;
                }
            }
        }
        return $disabledIds;
    }

    /**
     * Retrieve inactive category item ids
     *
     * @param Collection $collection
     * @param int $storeId
     * @return array
     * @since 2.0.0
     */
    protected function _getInactiveItemIds($collection, $storeId)
    {
        $linkField = $this->getMetadataPool()->getMetadata(CategoryInterface::class)->getLinkField();
        $intTable = $this->_coreResource->getTableName('catalog_category_entity_int');

        $select = $collection->getAllIdsSql()
            ->joinInner(
                ['d' => $intTable],
                "e.{$linkField} = d.{$linkField}",
                []
            )->joinLeft(
                ['c' => $intTable],
                "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = d.{$linkField}",
                []
            )->where(
                'd.attribute_id = :attribute_id'
            )->where(
                'd.store_id = :zero_store_id'
            )->where(
                $this->_conn->getCheckSql('c.value_id > 0', 'c.value', 'd.value') . ' = :cond'
            );

        return $this->_conn->fetchCol(
            $select,
            [
                'attribute_id' => $this->_catalogCategory->getIsActiveAttributeId(),
                'store_id' => $storeId,
                'zero_store_id' => 0,
                'cond' => 0
            ]
        );
    }

    /**
     * Check is category items active
     *
     * @param int $id
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    protected function _getItemIsActive($id)
    {
        if (!in_array($id, $this->_inactiveItems)) {
            return true;
        }
        return false;
    }

    /**
     * Get categories collection
     *
     * @param boolean $sorted
     * @return Collection
     * @since 2.0.0
     */
    public function getCollection($sorted = false)
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }

    /**
     * Clean unneeded collection
     *
     * @param Collection|array $object
     * @return void
     * @since 2.0.0
     */
    protected function _clean($object)
    {
        if (is_array($object)) {
            foreach ($object as $obj) {
                $this->_clean($obj);
            }
        }
        unset($object);
    }

    /**
     * Enter description here...
     *
     * @param Collection $collection
     * @return $this
     * @since 2.0.0
     */
    public function setCollection($collection)
    {
        if ($this->_collection !== null) {
            $this->_clean($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param boolean $sorted
     * @return Collection
     * @since 2.0.0
     */
    protected function _getDefaultCollection($sorted = false)
    {
        $this->_joinUrlRewriteIntoCollection = true;
        $collection = $this->_collectionFactory->create();
        $attributes = $this->_attributeConfig->getAttributeNames('catalog_category');
        $collection->addAttributeToSelect($attributes);

        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addAttributeToSort($sorted);
            } else {
                $collection->addAttributeToSort('name');
            }
        }

        return $collection;
    }

    /**
     * Executing parents move method and cleaning cache after it
     *
     * @param mixed $category
     * @param mixed $newParent
     * @param mixed $prevNode
     * @return void
     * @since 2.0.0
     */
    public function move($category, $newParent, $prevNode = null)
    {
        $this->_catalogCategory->move($category->getId(), $newParent->getId());
        parent::move($category, $newParent, $prevNode);

        $this->_afterMove();
    }

    /**
     * Move tree after
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _afterMove()
    {
        $this->_cache->clean([\Magento\Catalog\Model\Category::CACHE_TAG]);
        return $this;
    }

    /**
     * Load whole category tree, that will include specified categories ids.
     *
     * @param array $ids
     * @param bool $addCollectionData
     * @param bool $updateAnchorProductCount
     * @return $this|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function loadByIds($ids, $addCollectionData = true, $updateAnchorProductCount = true)
    {
        $levelField = $this->_conn->quoteIdentifier('level');
        $pathField = $this->_conn->quoteIdentifier('path');
        // load first two levels, if no ids specified
        if (empty($ids)) {
            $select = $this->_conn->select()->from($this->_table, 'entity_id')->where($levelField . ' <= 2');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        // collect paths of specified IDs and prepare to collect all their parents and neighbours
        $select = $this->_conn->select()->from($this->_table, ['path', 'level'])->where('entity_id IN (?)', $ids);
        $where = [$levelField . '=0' => true];

        foreach ($this->_conn->fetchAll($select) as $item) {
            $pathIds = explode('/', $item['path']);
            $level = (int)$item['level'];
            while ($level > 0) {
                $pathIds[count($pathIds) - 1] = '%';
                $path = implode('/', $pathIds);
                $where["{$levelField}={$level} AND {$pathField} LIKE '{$path}'"] = true;
                array_pop($pathIds);
                $level--;
            }
        }
        $where = array_keys($where);

        // get all required records
        if ($addCollectionData) {
            $select = $this->_createCollectionDataSelect();
        } else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ' . \Magento\Framework\DB\Select::SQL_ASC);
        }
        $select->where(implode(' OR ', $where));

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        if ($updateAnchorProductCount) {
            $this->_updateAnchorProductCount($arrNodes);
        }
        $childrenItems = [];
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '', null);
        return $this;
    }

    /**
     * Load array of category parents
     *
     * @param string $path
     * @param bool $addCollectionData
     * @param bool $withRootNode
     * @return array
     * @since 2.0.0
     */
    public function loadBreadcrumbsArray($path, $addCollectionData = true, $withRootNode = false)
    {
        $pathIds = explode('/', $path);
        if (!$withRootNode) {
            array_shift($pathIds);
        }
        $result = [];
        if (!empty($pathIds)) {
            if ($addCollectionData) {
                $select = $this->_createCollectionDataSelect(false);
            } else {
                $select = clone $this->_select;
            }
            $select->where(
                'e.entity_id IN(?)',
                $pathIds
            )->order(
                $this->_conn->getLengthSql('e.path') . ' ' . \Magento\Framework\DB\Select::SQL_ASC
            );
            $result = $this->_conn->fetchAll($select);
            $this->_updateAnchorProductCount($result);
        }
        return $result;
    }

    /**
     * Replace products count with self products count, if category is non-anchor
     *
     * @param array &$data
     * @return void
     * @since 2.0.0
     */
    protected function _updateAnchorProductCount(&$data)
    {
        foreach ($data as $key => $row) {
            if (0 === (int)$row['is_anchor']) {
                $data[$key]['product_count'] = $row['self_product_count'];
            }
        }
    }

    /**
     * Obtain select for categories with attributes.
     * By default everything from entity table is selected
     * + name, is_active and is_anchor
     * Also the correct product_count is selected, depending on is the category anchor or not.
     *
     * @param bool $sorted
     * @param array $optionalAttributes
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
     */
    protected function _createCollectionDataSelect($sorted = true, $optionalAttributes = [])
    {
        $meta = $this->getMetadataPool()->getMetadata(CategoryInterface::class);
        $linkField = $meta->getLinkField();

        $select = $this->_getDefaultCollection($sorted ? $this->_orderField : false)->getSelect();
        // add attributes to select
        $attributes = ['name', 'is_active', 'is_anchor'];
        if ($optionalAttributes) {
            $attributes = array_unique(array_merge($attributes, $optionalAttributes));
        }
        $resource = $this->_catalogCategory;
        foreach ($attributes as $attributeCode) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $attribute = $resource->getAttribute($attributeCode);
            // join non-static attribute table
            if (!$attribute->getBackend()->isStatic()) {
                $tableDefault = sprintf('d_%s', $attributeCode);
                $tableStore = sprintf('s_%s', $attributeCode);
                $valueExpr = $this->_conn->getCheckSql(
                    "{$tableStore}.value_id > 0",
                    "{$tableStore}.value",
                    "{$tableDefault}.value"
                );

                $select->joinLeft(
                    [$tableDefault => $attribute->getBackend()->getTable()],
                    sprintf(
                        '%1$s.' . $linkField . '=e.' . $linkField .
                        ' AND %1$s.attribute_id=%2$d AND %1$s.store_id=%3$d',
                        $tableDefault,
                        $attribute->getId(),
                        \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ),
                    [$attributeCode => 'value']
                )->joinLeft(
                    [$tableStore => $attribute->getBackend()->getTable()],
                    sprintf(
                        '%1$s.' . $linkField . '=e.' . $linkField .
                        ' AND %1$s.attribute_id=%2$d AND %1$s.store_id=%3$d',
                        $tableStore,
                        $attribute->getId(),
                        $this->getStoreId()
                    ),
                    [$attributeCode => $valueExpr]
                );
            }
        }

        // count children products qty plus self products qty
        $categoriesTable = $this->_coreResource->getTableName('catalog_category_entity');
        $categoriesProductsTable = $this->_coreResource->getTableName('catalog_category_product');

        $subConcat = $this->_conn->getConcatSql(['e.path', $this->_conn->quote('/%')]);
        $subSelect = $this->_conn->select()->from(
            ['see' => $categoriesTable],
            null
        )->joinLeft(
            ['scp' => $categoriesProductsTable],
            'see.entity_id=scp.category_id',
            ['COUNT(DISTINCT scp.product_id)']
        )->where(
            'see.entity_id = e.entity_id'
        )->orWhere(
            'see.path LIKE ?',
            $subConcat
        );
        $select->columns(['product_count' => $subSelect]);

        $subSelect = $this->_conn->select()->from(
            ['cp' => $categoriesProductsTable],
            'COUNT(cp.product_id)'
        )->where(
            'cp.category_id = e.entity_id'
        );

        $select->columns(['self_product_count' => $subSelect]);

        return $select;
    }

    /**
     * Get real existing category ids by specified ids
     *
     * @param array $ids
     * @return array
     * @since 2.0.0
     */
    public function getExistingCategoryIdsBySpecifiedIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $select = $this->_conn->select()->from($this->_table, ['entity_id'])->where('entity_id IN (?)', $ids);
        return $this->_conn->fetchCol($select);
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @since 2.1.0
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
