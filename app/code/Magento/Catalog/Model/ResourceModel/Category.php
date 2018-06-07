<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog category model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Model\Indexer\Category\Product\Processor;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Category extends AbstractResource
{
    /**
     * Category tree object
     *
     * @var \Magento\Framework\Data\Tree\Db
     */
    protected $_tree;

    /**
     * Catalog products table name
     *
     * @var string
     */
    protected $_categoryProductTable;

    /**
     * @var array[]
     */
    private $entitiesWhereAttributesIs;

    /**
     * Id of 'is_active' category attribute
     *
     * @var int
     */
    protected $_isActiveAttributeId = null;

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * Category tree factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\TreeFactory
     */
    protected $_categoryTreeFactory;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Category\AggregateCount
     */
    protected $aggregateCount;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * Category constructor.
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Factory $modelFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Category\TreeFactory $categoryTreeFactory
     * @param Category\CollectionFactory $categoryCollectionFactory
     * @param Processor $indexerProcessor
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        Processor $indexerProcessor,
        $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $data
        );
        $this->_categoryTreeFactory = $categoryTreeFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_eventManager = $eventManager;
        $this->connectionName  = 'catalog';
        $this->indexerProcessor = $indexerProcessor;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Entity type getter and lazy loader
     *
     * @return \Magento\Eav\Model\Entity\Type
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            $this->setType(\Magento\Catalog\Model\Category::ENTITY);
        }
        return parent::getEntityType();
    }

    /**
     * Category product table name getter
     *
     * @return string
     */
    public function getCategoryProductTable()
    {
        if (!$this->_categoryProductTable) {
            $this->_categoryProductTable = $this->getTable('catalog_category_product');
        }
        return $this->_categoryProductTable;
    }

    /**
     * Set store Id
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Retrieve category tree object
     *
     * @return \Magento\Framework\Data\Tree\Db
     */
    protected function _getTree()
    {
        if (!$this->_tree) {
            $this->_tree = $this->_categoryTreeFactory->create()->load();
        }
        return $this->_tree;
    }

    /**
     * Process category data before delete
     * update children count for parent category
     * delete child categories
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\DataObject $object)
    {
        parent::_beforeDelete($object);
        $this->getAggregateCount()->processDelete($object);
        $this->deleteChildren($object);
    }

    /**
     * Mark Category indexer as invalid to be picked up by cron.
     *
     * @param DataObject $object
     * @return $this
     */
    protected function _afterDelete(DataObject $object)
    {
        $this->indexerProcessor->markIndexerAsInvalid();
        return parent::_afterDelete($object);
    }

    /**
     * Delete children categories of specific category
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function deleteChildren(\Magento\Framework\DataObject $object)
    {
        if ($object->getSkipDeleteChildren()) {
            return $this;
        }

        $categories = $this->_categoryCollectionFactory->create();
        $categories->addAttributeToFilter('path', ['like' => $object->getPath() . '/%']);
        $childrenIds = $categories->getAllIds();
        foreach ($categories as $category) {
            $category->setSkipDeleteChildren(true);
            $category->delete();
        }

        /**
         * Add deleted children ids to object
         * This data can be used in after delete event
         */
        $object->setDeletedChildrenIds($childrenIds);
        return $this;
    }

    /**
     * Process category data before saving
     * prepare path and increment children count for parent categories
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $object)
    {
        parent::_beforeSave($object);

        if (!$object->getChildrenCount()) {
            $object->setChildrenCount(0);
        }
        $object->setAttributeSetId(
            $object->getAttributeSetId() ?: $this->getEntityType()->getDefaultAttributeSetId()
        );
        if ($object->isObjectNew()) {
            if ($object->getPosition() === null) {
                $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            }
            $path = explode('/', $object->getPath());
            $level = count($path)  - ($object->getId() ? 1 : 0);
            $toUpdateChild = array_diff($path, [$object->getId()]);

            if (!$object->hasPosition()) {
                $object->setPosition($this->_getMaxPosition(implode('/', $toUpdateChild)) + 1);
            }
            if (!$object->hasLevel()) {
                $object->setLevel($level);
            }
            if (!$object->hasParentId() && $level) {
                $object->setParentId($path[$level - 1]);
            }
            if (!$object->getId()) {
                $object->setPath($object->getPath() . '/');
            }

            $this->getConnection()->update(
                $this->getEntityTable(),
                ['children_count' => new \Zend_Db_Expr('children_count+1')],
                ['entity_id IN(?)' => $toUpdateChild]
            );
        }
        return $this;
    }

    /**
     * Process category data after save category object
     * save related products ids and update path value
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $object)
    {
        /**
         * Add identifier for new category
         */
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }

        $this->_saveCategoryProducts($object);
        return parent::_afterSave($object);
    }

    /**
     * Update path field
     *
     * @param \Magento\Catalog\Model\Category $object
     * @return $this
     */
    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->getConnection()->update(
                $this->getEntityTable(),
                ['path' => $object->getPath()],
                ['entity_id = ?' => $object->getId()]
            );
            $object->unsetData('path_ids');
        }
        return $this;
    }

    /**
     * Get maximum position of child categories by specific tree path
     *
     * @param string $path
     * @return int
     */
    protected function _getMaxPosition($path)
    {
        $connection = $this->getConnection();
        $positionField = $connection->quoteIdentifier('position');
        $level = count(explode('/', $path));
        $bind = ['c_level' => $level, 'c_path' => $path . '/%'];
        $select = $connection->select()->from(
            $this->getTable('catalog_category_entity'),
            'MAX(' . $positionField . ')'
        )->where(
            $connection->quoteIdentifier('path') . ' LIKE :c_path'
        )->where(
            $connection->quoteIdentifier('level') . ' = :c_level'
        );

        $position = $connection->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }
        return $position;
    }

    /**
     * Save category products relation
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveCategoryProducts($category)
    {
        $category->setIsChangedProductList(false);
        $id = $category->getId();
        /**
         * new category-product relationships
         */
        $products = $category->getPostedProducts();

        /**
         * Example re-save category
         */
        if ($products === null) {
            return $this;
        }

        /**
         * old category-product relationships
         */
        $oldProducts = $category->getProductsPosition();

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldProducts array)
         */
        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

        $connection = $this->getConnection();

        /**
         * Delete products from category
         */
        if (!empty($delete)) {
            $cond = ['product_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $connection->delete($this->getCategoryProductTable(), $cond);
        }

        /**
         * Add products to category
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'position' => (int)$position,
                ];
            }
            $connection->insertMultiple($this->getCategoryProductTable(), $data);
        }

        /**
         * Update product positions in category
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $productId => $position) {
                $delta = $position - $oldProducts[$productId];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $productId;
            }

            foreach ($newPositions as $delta => $productIds) {
                $bind = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['category_id = ?' => (int)$id, 'product_id IN (?)' => $productIds];
                $connection->update($this->getCategoryProductTable(), $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $productIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->_eventManager->dispatch(
                'catalog_category_change_products',
                ['category' => $category, 'product_ids' => $productIds]
            );

            $category->setChangedProductIds($productIds);
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $category->setIsChangedProductList(true);

            /**
             * Setting affected products to category for third party engine index refresh
             */
            $productIds = array_keys($insert + $delete + $update);
            $category->setAffectedProductIds($productIds);
        }
        return $this;
    }

    /**
     * Get positions of associated to category products
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function getProductsPosition($category)
    {
        $select = $this->getConnection()->select()->from(
            $this->getCategoryProductTable(),
            ['product_id', 'position']
        )->where(
            'category_id = :category_id'
        );
        $bind = ['category_id' => (int)$category->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Get chlden categories count
     *
     * @param int $categoryId
     * @return int
     */
    public function getChildrenCount($categoryId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'children_count'
        )->where(
            'entity_id = :entity_id'
        );
        $bind = ['entity_id' => $categoryId];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Check if category id exist
     *
     * @param int $entityId
     * @return bool
     */
    public function checkId($entityId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id = :entity_id'
        );
        $bind = ['entity_id' => $entityId];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Check array of category identifiers
     *
     * @param array $ids
     * @return array
     */
    public function verifyIds(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id IN(?)',
            $ids
        );

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Get count of active/not active children categories
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $isActiveFlag
     * @return int
     */
    public function getChildrenAmount($category, $isActiveFlag = true)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $attributeId = $this->getIsActiveAttributeId();
        $table = $this->getTable([$this->getEntityTablePrefix(), 'int']);
        $connection = $this->getConnection();
        $checkSql = $connection->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $linkField = $this->getLinkField();
        $bind = [
            'attribute_id' => $attributeId,
            'store_id' => $storeId,
            'active_flag' => $isActiveFlag,
            'c_path' => $category->getPath() . '/%',
        ];
        $select = $connection->select()->from(
            ['m' => $this->getEntityTable()],
            ['COUNT(m.entity_id)']
        )->joinLeft(
            ['d' => $table],
            "d.attribute_id = :attribute_id AND d.store_id = 0 AND d.{$linkField} = m.{$linkField}",
            []
        )->joinLeft(
            ['c' => $table],
            "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = m.{$linkField}",
            []
        )->where(
            'm.path LIKE :c_path'
        )->where(
            $checkSql . ' = :active_flag'
        );

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    public function getIsActiveAttributeId()
    {
        if ($this->_isActiveAttributeId === null) {
            $this->_isActiveAttributeId = (int)$this->_eavConfig
                ->getAttribute($this->getEntityType(), 'is_active')
                ->getAttributeId();
        }
        return $this->_isActiveAttributeId;
    }

    /**
     * Return entities where attribute value is
     *
     * @param array|int $entityIdsFilter
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param mixed $expectedValue
     * @return array
     */
    public function findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue)
    {
        // @codingStandardsIgnoreStart
        $serializeData = $this->serializer->serialize($entityIdsFilter);
        $entityIdsFilterHash = md5($serializeData);
        // @codingStandardsIgnoreEnd

        if (!isset($this->entitiesWhereAttributesIs[$entityIdsFilterHash][$attribute->getId()][$expectedValue])) {
            $linkField = $this->getLinkField();
            $bind = ['attribute_id' => $attribute->getId(), 'value' => $expectedValue];
            $selectEntities = $this->getConnection()->select()->from(
                ['ce' => $this->getTable('catalog_category_entity')],
                ['entity_id']
            )->joinLeft(
                ['ci' => $attribute->getBackend()->getTable()],
                "ci.{$linkField} = ce.{$linkField} AND attribute_id = :attribute_id",
                ['value']
            )->where(
                'ci.value = :value'
            )->where(
                'ce.entity_id IN (?)',
                $entityIdsFilter
            );
            $this->entitiesWhereAttributesIs[$entityIdsFilterHash][$attribute->getId()][$expectedValue] =
                $this->getConnection()->fetchCol($selectEntities, $bind);
        }

        return $this->entitiesWhereAttributesIs[$entityIdsFilterHash][$attribute->getId()][$expectedValue];
    }

    /**
     * Get products count in category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    public function getProductCount($category)
    {
        $productTable = $this->_resource->getTableName('catalog_category_product');

        $select = $this->getConnection()->select()->from(
            ['main_table' => $productTable],
            [new \Zend_Db_Expr('COUNT(main_table.product_id)')]
        )->where(
            'main_table.category_id = :category_id'
        );

        $bind = ['category_id' => (int)$category->getId()];
        $counts = $this->getConnection()->fetchOne($select, $bind);

        return intval($counts);
    }

    /**
     * Retrieve categories
     *
     * @param integer $parent
     * @param integer $recursionLevel
     * @param boolean|string $sorted
     * @param boolean $asCollection
     * @param boolean $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted = false, $asCollection = false, $toLoad = true)
    {
        $tree = $this->_categoryTreeFactory->create();
        /* @var $tree \Magento\Catalog\Model\ResourceModel\Category\Tree */
        $nodes = $tree->loadNode($parent)->loadChildren($recursionLevel)->getChildren();

        $tree->addCollectionData(null, $sorted, $parent, $toLoad, true);

        if ($asCollection) {
            return $tree->getCollection();
        }
        return $nodes;
    }

    /**
     * Return parent categories of category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Framework\DataObject[]
     */
    public function getParentCategories($category)
    {
        $pathIds = array_reverse(explode(',', $category->getPathInStore()));
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories = $this->_categoryCollectionFactory->create();
        return $categories->setStore(
            $this->_storeManager->getStore()
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'url_key'
        )->addFieldToFilter(
            'entity_id',
            ['in' => $pathIds]
        )->addFieldToFilter(
            'is_active',
            1
        )->load()->getItems();
    }

    /**
     * Return parent category of current category with own custom design settings
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     */
    public function getParentDesignCategory($category)
    {
        $pathIds = array_reverse($category->getPathIds());
        $collection = $category->getCollection()->setStore(
            $this->_storeManager->getStore()
        )->addAttributeToSelect(
            'custom_design'
        )->addAttributeToSelect(
            'custom_design_from'
        )->addAttributeToSelect(
            'custom_design_to'
        )->addAttributeToSelect(
            'page_layout'
        )->addAttributeToSelect(
            'custom_layout_update'
        )->addAttributeToSelect(
            'custom_apply_to_products'
        )->addFieldToFilter(
            'entity_id',
            ['in' => $pathIds]
        )->addAttributeToFilter(
            'custom_use_parent_settings',
            [['eq' => 0], ['null' => 0]],
            'left'
        )->addFieldToFilter(
            'level',
            ['neq' => 0]
        )->setOrder(
            'level',
            'DESC'
        )->load();
        return $collection->getFirstItem();
    }

    /**
     * Return child categories
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getChildrenCategories($category)
    {
        $collection = $category->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
            'url_key'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'all_children'
        )->addAttributeToSelect(
            'is_anchor'
        )->addAttributeToFilter(
            'is_active',
            1
        )->addIdFilter(
            $category->getChildren()
        )->setOrder(
            'position',
            \Magento\Framework\DB\Select::SQL_ASC
        )->joinUrlRewrite();

        return $collection;
    }

    /**
     * Return children ids of category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param boolean $recursive
     * @return array
     */
    public function getChildren($category, $recursive = true)
    {
        $linkField = $this->getLinkField();
        $attributeId = $this->getIsActiveAttributeId();
        $backendTable = $this->getTable([$this->getEntityTablePrefix(), 'int']);
        $connection = $this->getConnection();
        $checkSql = $connection->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $bind = [
            'attribute_id' => $attributeId,
            'store_id' => $category->getStoreId(),
            'scope' => 1,
            'c_path' => $category->getPath() . '/%',
        ];
        $select = $this->getConnection()->select()->from(
            ['m' => $this->getEntityTable()],
            'entity_id'
        )->joinLeft(
            ['d' => $backendTable],
            "d.attribute_id = :attribute_id AND d.store_id = 0 AND d.{$linkField} = m.{$linkField}",
            []
        )->joinLeft(
            ['c' => $backendTable],
            "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = m.{$linkField}",
            []
        )->where(
            $checkSql . ' = :scope'
        )->where(
            $connection->quoteIdentifier('path') . ' LIKE :c_path'
        );
        if (!$recursive) {
            $select->where($connection->quoteIdentifier('level') . ' <= :c_level');
            $bind['c_level'] = $category->getLevel() + 1;
        }

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Return all children ids of category (with category id)
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function getAllChildren($category)
    {
        $children = $this->getChildren($category);
        $myId = [$category->getId()];
        $children = array_merge($myId, $children);

        return $children;
    }

    /**
     * Check is category in list of store categories
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return boolean
     */
    public function isInRootCategoryList($category)
    {
        $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();

        return in_array($rootCategoryId, $category->getParentIds());
    }

    /**
     * Check category is forbidden to delete.
     * If category is root and assigned to store group return false
     *
     * @param integer $categoryId
     * @return boolean
     */
    public function isForbiddenToDelete($categoryId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('store_group'),
            ['group_id']
        )->where(
            'root_category_id = :root_category_id'
        );
        $result = $this->getConnection()->fetchOne($select, ['root_category_id' => $categoryId]);

        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Get category path value by its id
     *
     * @param int $categoryId
     * @return string
     */
    public function getCategoryPathById($categoryId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            ['path']
        )->where(
            'entity_id = :entity_id'
        );
        $bind = ['entity_id' => (int)$categoryId];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Move category to another parent node
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\Category $newParent
     * @param null|int $afterCategoryId
     * @return $this
     */
    public function changeParent(
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Model\Category $newParent,
        $afterCategoryId = null
    ) {
        $childrenCount = $this->getChildrenCount($category->getId()) + 1;
        $table = $this->getEntityTable();
        $connection = $this->getConnection();
        $levelFiled = $connection->quoteIdentifier('level');
        $pathField = $connection->quoteIdentifier('path');

        /**
         * Decrease children count for all old category parent categories
         */
        $connection->update(
            $table,
            ['children_count' => new \Zend_Db_Expr('children_count - ' . $childrenCount)],
            ['entity_id IN(?)' => $category->getParentIds()]
        );

        /**
         * Increase children count for new category parents
         */
        $connection->update(
            $table,
            ['children_count' => new \Zend_Db_Expr('children_count + ' . $childrenCount)],
            ['entity_id IN(?)' => $newParent->getPathIds()]
        );

        $position = $this->_processPositions($category, $newParent, $afterCategoryId);

        $newPath = sprintf('%s/%s', $newParent->getPath(), $category->getId());
        $newLevel = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $category->getLevel();

        /**
         * Update children nodes path
         */
        $connection->update(
            $table,
            [
                'path' => new \Zend_Db_Expr(
                    'REPLACE(' . $pathField . ',' . $connection->quote(
                        $category->getPath() . '/'
                    ) . ', ' . $connection->quote(
                        $newPath . '/'
                    ) . ')'
                ),
                'level' => new \Zend_Db_Expr($levelFiled . ' + ' . $levelDisposition)
            ],
            [$pathField . ' LIKE ?' => $category->getPath() . '/%']
        );
        /**
         * Update moved category data
         */
        $data = [
            'path' => $newPath,
            'level' => $newLevel,
            'position' => $position,
            'parent_id' => $newParent->getId(),
        ];
        $connection->update($table, $data, ['entity_id = ?' => $category->getId()]);

        // Update category object to new data
        $category->addData($data);
        $category->unsetData('path_ids');

        return $this;
    }

    /**
     * Process positions of old parent category children and new parent category children.
     * Get position for moved category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Catalog\Model\Category $newParent
     * @param null|int $afterCategoryId
     * @return int
     */
    protected function _processPositions($category, $newParent, $afterCategoryId)
    {
        $table = $this->getEntityTable();
        $connection = $this->getConnection();
        $positionField = $connection->quoteIdentifier('position');

        $bind = ['position' => new \Zend_Db_Expr($positionField . ' - 1')];
        $where = [
            'parent_id = ?' => $category->getParentId(),
            $positionField . ' > ?' => $category->getPosition(),
        ];
        $connection->update($table, $bind, $where);

        /**
         * Prepare position value
         */
        if ($afterCategoryId) {
            $select = $connection->select()->from($table, 'position')->where('entity_id = :entity_id');
            $position = $connection->fetchOne($select, ['entity_id' => $afterCategoryId]);
            $position += 1;
        } else {
            $position = 1;
        }

        $bind = ['position' => new \Zend_Db_Expr($positionField . ' + 1')];
        $where = ['parent_id = ?' => $newParent->getId(), $positionField . ' >= ?' => $position];
        $connection->update($table, $bind, $where);

        return $position;
    }

    /**
     * Get total number of persistent categories in the system, excluding the default category
     *
     * @return int
     */
    public function countVisible()
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getEntityTable(), 'COUNT(*)')->where('parent_id != ?', 0);
        return (int)$connection->fetchOne($select);
    }

    /**
     * Reset firstly loaded attributes
     *
     * @param \Magento\Framework\DataObject $object
     * @param integer $entityId
     * @param array|null $attributes
     * @return $this
     */
    public function load($object, $entityId, $attributes = [])
    {
        $this->_attributes = [];
        $select = $this->_getLoadRowSelect($object, $entityId);
        $row = $this->getConnection()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        $this->loadAttributesForObject($attributes, $object);
        $object = $this->getEntityManager()->load($object, $entityId);
        if (!$this->getEntityManager()->has($object)) {
            $object->isObjectNew(true);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->getEntityManager()->delete($object);
        $this->_eventManager->dispatch(
            'catalog_category_delete_after_done',
            ['product' => $object, 'category' => $object]
        );
        return $this;
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->getEntityManager()->save($object);
        return $this;
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\EntityManager::class);
        }
        return $this->entityManager;
    }

    /**
     * @return Category\AggregateCount
     */
    private function getAggregateCount()
    {
        if (null === $this->aggregateCount) {
            $this->aggregateCount = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\ResourceModel\Category\AggregateCount::class);
        }
        return $this->aggregateCount;
    }
}
