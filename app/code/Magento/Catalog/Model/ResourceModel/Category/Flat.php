<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;

/**
 * Category flat model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Flat extends \Magento\Indexer\Model\ResourceModel\AbstractResource
{
    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Loaded
     *
     * @var boolean
     */
    protected $_loaded = false;

    /**
     * Nodes
     *
     * @var array
     */
    protected $_nodes = [];

    /**
     * Inactive categories ids
     *
     * @var array
     */
    protected $_inactiveCategoryIds;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     * @deprecated 100.0.2
     */
    protected $_categoryCollectionFactory;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Flat\CollectionFactory
     */
    private $categoryFlatCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string $connectionName
     * @param \Magento\Catalog\Model\ResourceModel\Category\Flat\CollectionFactory|null $categoryFlatCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null,
        \Magento\Catalog\Model\ResourceModel\Category\Flat\CollectionFactory $categoryFlatCollectionFactory = null
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_catalogConfig = $catalogConfig;
        $this->_eventManager = $eventManager;
        $this->categoryFlatCollectionFactory = $categoryFlatCollectionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\ResourceModel\Category\Flat\CollectionFactory::class);
        parent::__construct($context, $tableStrategy, $connectionName);
    }

    /**
     * Resource initializations
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_flat', 'entity_id');
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return $this
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
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return (int)$this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Get main table name
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->getMainStoreTable($this->getStoreId());
    }

    /**
     * Return name of table for given $storeId.
     *
     * @param integer $storeId
     * @return string
     */
    public function getMainStoreTable($storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        if (is_string($storeId)) {
            $storeId = (int) $storeId;
        }

        if ($storeId) {
            $suffix = sprintf('store_%d', $storeId);
            $table = $this->getTable('catalog_category_flat_' . $suffix);
        } else {
            $table = parent::getMainTable();
        }

        return $table;
    }

    /**
     * Add inactive categories ids
     *
     * @param array $ids
     * @return $this
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
     */
    public function getInactiveCategoryIds()
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }

        return $this->_inactiveCategoryIds;
    }

    /**
     * Load nodes by parent id
     *
     * @param \Magento\Catalog\Model\Category|int $parentNode
     * @param integer $recursionLevel
     * @param integer $storeId
     * @param bool $skipMenuFilter
     * @return array
     */
    protected function _loadNodes($parentNode = null, $recursionLevel = 0, $storeId = 0, $skipMenuFilter = false)
    {
        $_conn = $this->getConnection();
        $startLevel = 1;
        $parentPath = '';
        if ($parentNode instanceof \Magento\Catalog\Model\Category) {
            $parentPath = $parentNode->getPath();
            $startLevel = $parentNode->getLevel();
        } elseif (is_numeric($parentNode)) {
            $selectParent = $_conn->select()->from(
                $this->getMainStoreTable($storeId)
            )->where(
                'entity_id = ?',
                $parentNode
            )->where(
                'store_id = ?',
                $storeId
            );
            $parentNode = $_conn->fetchRow($selectParent);
            if ($parentNode) {
                $parentPath = $parentNode['path'];
                $startLevel = $parentNode['level'];
            }
        }
        $select = $_conn->select()->from(
            ['main_table' => $this->getMainStoreTable($storeId)],
            [
                'entity_id',
                new \Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('name')),
                new \Zend_Db_Expr('main_table.' . $_conn->quoteIdentifier('path')),
                'is_active',
                'is_anchor'
            ]
        )->joinLeft(
            ['url_rewrite' => $this->getTable('url_rewrite')],
            'url_rewrite.entity_id = main_table.entity_id AND url_rewrite.is_autogenerated = 1'
            . $_conn->quoteInto(' AND url_rewrite.store_id = ?', $storeId)
            . $_conn->quoteInto(' AND url_rewrite.entity_type = ?', CategoryUrlRewriteGenerator::ENTITY_TYPE),
            ['request_path' => 'url_rewrite.request_path']
        )->where('main_table.is_active = 1');

        if (false == $skipMenuFilter) {
            $select->where('main_table.include_in_menu = ?', '1');
        }

        $select->order('main_table.position');

        if ($parentPath) {
            $select->where($_conn->quoteInto("main_table.path like ?", "{$parentPath}/%"));
        }
        if ($recursionLevel != 0) {
            $levelField = $_conn->quoteIdentifier('level');
            $select->where($levelField . ' <= ?', $startLevel + $recursionLevel);
        }

        $inactiveCategories = $this->getInactiveCategoryIds();

        if (!empty($inactiveCategories)) {
            $select->where('main_table.entity_id NOT IN (?)', $inactiveCategories);
        }

        // Allow extensions to modify select (e.g. add custom category attributes to select)
        $this->_eventManager->dispatch('catalog_category_flat_loadnodes_before', ['select' => $select]);

        $arrNodes = $_conn->fetchAll($select);
        $nodes = [];
        foreach ($arrNodes as $node) {
            $node['id'] = $node['entity_id'];
            $nodes[$node['id']] = $this->_categoryFactory->create()->setData($node);
        }

        return $nodes;
    }

    /**
     * Creating sorted array of nodes
     *
     * @param array $children
     * @param string $path
     * @param \Magento\Framework\DataObject $parent
     * @return void
     */
    public function addChildNodes($children, $path, $parent)
    {
        if (isset($children[$path])) {
            foreach ($children[$path] as $child) {
                $childrenNodes = $parent->getChildrenNodes();
                if ($childrenNodes && isset($childrenNodes[$child->getId()])) {
                    $childrenNodes[$child['entity_id']]->setChildrenNodes([$child->getId() => $child]);
                } else {
                    if ($childrenNodes) {
                        $childrenNodes[$child->getId()] = $child;
                    } else {
                        $childrenNodes = [$child->getId() => $child];
                    }
                    $parent->setChildrenNodes($childrenNodes);
                }

                if ($path) {
                    $childrenPath = explode('/', $path);
                } else {
                    $childrenPath = [];
                }
                $childrenPath[] = $child->getId();
                $childrenPath = implode('/', $childrenPath);
                $this->addChildNodes($children, $childrenPath, $child);
            }
        }
    }

    /**
     * Return sorted array of nodes
     *
     * @param integer|null $parentId
     * @param integer $recursionLevel
     * @param integer $storeId
     * @return array
     */
    public function getNodes($parentId, $recursionLevel = 0, $storeId = 0)
    {
        if (!$this->_loaded) {
            $selectParent = $this->getConnection()->select()->from(
                $this->getMainStoreTable($storeId)
            )->where(
                'entity_id = ?',
                $parentId
            );
            if ($parentNode = $this->getConnection()->fetchRow($selectParent)) {
                $parentNode['id'] = $parentNode['entity_id'];
                $parentNode = $this->_categoryFactory->create()->setData($parentNode);
                $this->_nodes[$parentNode->getId()] = $parentNode;
                $nodes = $this->_loadNodes($parentNode, $recursionLevel, $storeId);
                $childrenItems = [];
                foreach ($nodes as $node) {
                    $pathToParent = explode('/', $node->getPath());
                    array_pop($pathToParent);
                    $pathToParent = implode('/', $pathToParent);
                    $childrenItems[$pathToParent][] = $node;
                }
                $this->addChildNodes($childrenItems, $parentNode->getPath(), $parentNode);
                $childrenNodes = $this->_nodes[$parentNode->getId()];
                if ($childrenNodes->getChildrenNodes()) {
                    $this->_nodes = $childrenNodes->getChildrenNodes();
                } else {
                    $this->_nodes = [];
                }
                $this->_loaded = true;
            }
        }
        return $this->_nodes;
    }

    /**
     * Return array or collection of categories
     *
     * @param integer $parent
     * @param integer $recursionLevel
     * @param boolean|string $sorted
     * @param boolean $asCollection
     * @param boolean $toLoad
     * @return array|\Magento\Framework\Data\Collection
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted = false, $asCollection = false, $toLoad = true)
    {
        if ($asCollection) {
            $select = $this->getConnection()->select()->from(
                ['mt' => $this->getMainStoreTable($this->getStoreId())],
                ['path']
            )->where(
                'mt.entity_id = ?',
                $parent
            );
            $parentPath = $this->getConnection()->fetchOne($select);

            $collection = $this->categoryFlatCollectionFactory
                ->create()
                ->addNameToResult()
                ->addUrlRewriteToResult()
                ->addParentPathFilter($parentPath)
                ->addStoreFilter()
                ->addIsActiveFilter()
                ->addAttributeToFilter('include_in_menu', 1)
                ->addSortedField($sorted);
            if ($toLoad) {
                return $collection->load();
            }
            return $collection;
        }
        return $this->getNodes($parent, $recursionLevel, $this->_storeManager->getStore()->getId());
    }

    /**
     * Return node with id $nodeId
     *
     * @param integer $nodeId
     * @param array $nodes
     * @return \Magento\Framework\DataObject
     */
    public function getNodeById($nodeId, $nodes = null)
    {
        if ($nodes === null) {
            $nodes = $this->getNodes($nodeId);
        }
        if (isset($nodes[$nodeId])) {
            return $nodes[$nodeId];
        }
        foreach ($nodes as $node) {
            if ($node->getChildrenNodes()) {
                return $this->getNodeById($nodeId, $node->getChildrenNodes());
            }
        }
        return [];
    }

    /**
     * Retrieve attribute instance
     *
     * Special for non static flat table.
     *
     * @param mixed $attribute
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttribute($attribute)
    {
        return $this->_catalogConfig->getAttribute(\Magento\Catalog\Model\Category::ENTITY, $attribute);
    }

    /**
     * Get count of active/not active children categories
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $isActiveFlag
     * @return integer
     */
    public function getChildrenAmount($category, $isActiveFlag = true)
    {
        $_table = $this->getMainStoreTable($category->getStoreId());
        $select = $this->getConnection()->select()->from(
            $_table,
            "COUNT({$_table}.entity_id)"
        )->where(
            "{$_table}.path LIKE ?",
            $category->getPath() . '/%'
        )->where(
            "{$_table}.is_active = ?",
            (int)$isActiveFlag
        );
        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * Get products count in category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return integer
     */
    public function getProductCount($category)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_category_product'),
            "COUNT({$this->getTable('catalog_category_product')}.product_id)"
        )->where(
            "{$this->getTable('catalog_category_product')}.category_id = ?",
            $category->getId()
        )->group(
            "{$this->getTable('catalog_category_product')}.category_id"
        );
        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * Return parent categories of category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $isActive
     * @return \Magento\Catalog\Model\Category[]
     */
    public function getParentCategories($category, $isActive = true)
    {
        $categories = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getMainStoreTable($category->getStoreId())],
            ['main_table.entity_id', 'main_table.name']
        )->joinLeft(
            ['url_rewrite' => $this->getTable('url_rewrite')],
            'url_rewrite.entity_id = main_table.entity_id AND url_rewrite.is_autogenerated = 1'
            . $connection->quoteInto(' AND url_rewrite.store_id = ?', $category->getStoreId())
            . $connection->quoteInto(' AND url_rewrite.entity_type = ?', CategoryUrlRewriteGenerator::ENTITY_TYPE),
            ['request_path' => 'url_rewrite.request_path']
        )->where(
            'main_table.entity_id IN (?)',
            array_reverse(explode(',', $category->getPathInStore()))
        );
        if ($isActive) {
            $select->where('main_table.is_active = ?', '1');
        }
        $select->order('main_table.path ASC');
        $result = $this->getConnection()->fetchAll($select);
        foreach ($result as $row) {
            $row['id'] = $row['entity_id'];
            $categories[$row['entity_id']] = $this->_categoryFactory->create()->setData($row);
        }
        return $categories;
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
        $collection = clone $category->getCollection();
        $collection->setMainTable(
            $this->getMainStoreTable($category->getStoreId())
        )->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'entity_id',
            ['in' => $pathIds]
        )->addFieldToFilter(
            'custom_use_parent_settings',
            [['eq' => 0], ['null' => 0]]
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
     * Return children categories of category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category[]
     */
    public function getChildrenCategories($category)
    {
        $categories = $this->_loadNodes($category, 1, $category->getStoreId(), true);
        return $categories;
    }

    /**
     * Check is category in list of store categories
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return boolean
     */
    public function isInRootCategoryList($category)
    {
        $pathIds = $category->getParentIds();
        return in_array($this->_storeManager->getStore()->getRootCategoryId(), $pathIds);
    }

    /**
     * Return children ids of category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param bool $recursive
     * @param bool $isActive
     * @param bool $sortByPosition
     * @return array
     */
    public function getChildren($category, $recursive = true, $isActive = true, $sortByPosition = false)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainStoreTable($category->getStoreId()),
            'entity_id'
        )->where(
            'path LIKE ?',
            "{$category->getPath()}/%"
        );
        if (!$recursive) {
            $select->where('level <= ?', $category->getLevel() + 1);
        }
        if ($isActive) {
            $select->where('is_active = ?', '1');
        }
        if ($sortByPosition) {
            $select->order('position ASC');
        }
        $_categories = $this->getConnection()->fetchAll($select);
        $categoriesIds = [];
        foreach ($_categories as $_category) {
            $categoriesIds[] = $_category['entity_id'];
        }
        return $categoriesIds;
    }

    /**
     * Return all children ids of category (with category id)
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function getAllChildren($category)
    {
        $categoriesIds = $this->getChildren($category);
        $myId = [$category->getId()];
        $categoriesIds = array_merge($myId, $categoriesIds);

        return $categoriesIds;
    }

    /**
     * Check if category id exist
     *
     * @param int $id
     * @return bool
     */
    public function checkId($id)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainStoreTable($this->getStoreId()),
            'entity_id'
        )->where(
            'entity_id=?',
            $id
        );
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Retrieve anchors above
     *
     * @param array $filterIds
     * @param int $storeId
     * @return array
     */
    public function getAnchorsAbove(array $filterIds, $storeId = 0)
    {
        $select = $this->getConnection()->select()->from(
            ['e' => $this->getMainStoreTable($storeId)],
            'entity_id'
        )->where(
            'is_anchor = ?',
            1
        )->where(
            'entity_id IN (?)',
            $filterIds
        );

        return $this->getConnection()->fetchCol($select);
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
            $this->getTable('catalog_category_product'),
            ['product_id', 'position']
        )->where(
            "{$this->getTable('catalog_category_product')}.category_id = ?",
            $category->getId()
        );
        $websiteId = $category->getStore()->getWebsiteId();
        if ($websiteId) {
            $select->join(
                ['product_website' => $this->getTable('catalog_product_website')],
                "product_website.product_id = {$this->getTable('catalog_category_product')}.product_id",
                []
            )->where(
                'product_website.website_id = ?',
                $websiteId
            );
        }
        $bind = ['category_id' => (int)$category->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }
}
