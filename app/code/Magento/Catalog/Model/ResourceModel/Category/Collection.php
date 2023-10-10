<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\ScopeInterface;

/**
 * Category resource collection
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Event prefix name
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_category_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'category_collection';

    /**
     * Name of product table
     *
     * @var string
     */
    private $_productTable;

    /**
     * Store id, that we should count products on
     *
     * @var int
     */
    protected $_productStoreId;

    /**
     * Name of product website table
     *
     * @var string
     */
    private $_productWebsiteTable;

    /**
     * Load with product count flag
     *
     * @var boolean
     */
    protected $_loadWithProductCount = false;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Visibility
     */
    private $catalogProductVisibility;

    /**
     * Constructor
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Visibility|null $catalogProductVisibility
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig = null,
        Visibility $catalogProductVisibility = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $connection
        );
        $this->scopeConfig = $scopeConfig ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->catalogProductVisibility = $catalogProductVisibility ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(Visibility::class);
    }

    /**
     * Init collection and determine table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Category::class, \Magento\Catalog\Model\ResourceModel\Category::class);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        parent::_resetState();
        $this->_productTable = null;
        $this->_productStoreId = null;
        $this->_productWebsiteTable = null;
        $this->_loadWithProductCount = false;
    }

    /**
     * Add Id filter
     *
     * @param array $categoryIds
     * @return $this
     */
    public function addIdFilter($categoryIds)
    {
        if (is_array($categoryIds)) {
            if (empty($categoryIds)) {
                $condition = '';
            } else {
                $condition = ['in' => $categoryIds];
            }
        } elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        } elseif (is_string($categoryIds)) {
            $ids = explode(',', $categoryIds);
            if (count($ids) == 0) {
                $condition = $categoryIds;
            } else {
                $condition = ['in' => $ids];
            }
        }
        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    /**
     * Set flag for loading product count
     *
     * @param boolean $flag
     * @return $this
     */
    public function setLoadProductCount($flag)
    {
        $this->_loadWithProductCount = $flag;
        return $this;
    }

    /**
     * Before collection load
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_before', [$this->_eventObject => $this]);
        return parent::_beforeLoad();
    }

    /**
     * After collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', [$this->_eventObject => $this]);

        return parent::_afterLoad();
    }

    /**
     * Set id of the store that we should count products on
     *
     * @param int $storeId
     * @return $this
     */
    public function setProductStoreId($storeId)
    {
        $this->_productStoreId = $storeId;
        return $this;
    }

    /**
     * Get id of the store that we should count products on
     *
     * @return int
     */
    public function getProductStoreId()
    {
        if ($this->_productStoreId === null) {
            $this->_productStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        return $this->_productStoreId;
    }

    /**
     * Load collection
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        if ($this->_loadWithProductCount) {
            $this->addAttributeToSelect('all_children');
            $this->addAttributeToSelect('is_anchor');
        }

        parent::load($printQuery, $logQuery);

        if ($this->_loadWithProductCount) {
            $this->_loadProductCount();
        }

        return $this;
    }

    /**
     * Load categories product count
     *
     * @return void
     */
    protected function _loadProductCount()
    {
        $this->loadProductCount($this->_items, true, true);
    }

    /**
     * Load product count for specified items
     *
     * @param array $items
     * @param boolean $countRegular get product count for regular (non-anchor) categories
     * @param boolean $countAnchor get product count for anchor categories
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadProductCount($items, $countRegular = true, $countAnchor = true)
    {
        $anchor = [];
        $regular = [];
        $websiteId = $this->_storeManager->getStore($this->getProductStoreId())->getWebsiteId();

        foreach ($items as $item) {
            if ($item->getIsAnchor()) {
                $anchor[$item->getId()] = $item;
            } else {
                $regular[$item->getId()] = $item;
            }
        }

        if ($countRegular) {
            // Retrieve regular categories product counts
            $regularIds = array_keys($regular);
            if (!empty($regularIds)) {
                $select = $this->_conn->select();
                $select->from(
                    ['main_table' => $this->getProductTable()],
                    ['category_id', new \Zend_Db_Expr('COUNT(main_table.product_id)')]
                )->where(
                    $this->_conn->quoteInto('main_table.category_id IN(?)', $regularIds)
                )->group(
                    'main_table.category_id'
                );
                if ($websiteId) {
                    $select->join(
                        ['w' => $this->getProductWebsiteTable()],
                        'main_table.product_id = w.product_id',
                        []
                    )->where(
                        'w.website_id = ?',
                        $websiteId
                    );
                }
                $counts = $this->_conn->fetchPairs($select);
                foreach ($regular as $item) {
                    if (isset($counts[$item->getId()])) {
                        $item->setProductCount($counts[$item->getId()]);
                    } else {
                        $item->setProductCount(0);
                    }
                }
            }
        }

        if ($countAnchor) {
            // Retrieve Anchor categories product counts
            $categoryIds = array_keys($anchor);
            $countSelect = $this->getProductsCountQuery($categoryIds, (bool)$websiteId);
            $categoryProductsCount = $this->_conn->fetchPairs($countSelect);
            foreach ($anchor as $item) {
                $productsCount = isset($categoryProductsCount[$item->getId()])
                    ? (int)$categoryProductsCount[$item->getId()]
                    : $this->getProductsCountFromCategoryTable($item, $websiteId);
                $item->setProductCount($productsCount);
            }
        }
        return $this;
    }

    /**
     * Add category path filter
     *
     * @param string $regexp
     * @return $this
     */
    public function addPathFilter($regexp)
    {
        $this->addFieldToFilter('path', ['regexp' => $regexp]);
        return $this;
    }

    /**
     * Joins url rewrite rules to collection
     *
     * @return $this
     */
    public function joinUrlRewrite()
    {
        $this->joinTable(
            'url_rewrite',
            'entity_id = entity_id',
            ['request_path'],
            sprintf(
                '{{table}}.is_autogenerated = 1 AND {{table}}.store_id = %d AND {{table}}.entity_type = \'%s\'',
                $this->getStoreId(),
                CategoryUrlRewriteGenerator::ENTITY_TYPE
            ),
            'left'
        );
        return $this;
    }

    /**
     * Add active category filter
     *
     * @return $this
     */
    public function addIsActiveFilter()
    {
        $this->addAttributeToFilter('is_active', 1);
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_add_is_active_filter',
            [$this->_eventObject => $this]
        );
        return $this;
    }

    /**
     * Add name attribute to result
     *
     * @return $this
     */
    public function addNameToResult()
    {
        $this->addAttributeToSelect('name');
        return $this;
    }

    /**
     * Add url rewrite rules to collection
     *
     * @return $this
     */
    public function addUrlRewriteToResult()
    {
        $this->joinUrlRewrite();
        return $this;
    }

    /**
     * Add category path filter
     *
     * @param array|string $paths
     * @return $this
     */
    public function addPathsFilter($paths)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        $connection = $this->getResource()->getConnection();
        $cond = [];
        foreach ($paths as $path) {
            $cond[] = $connection->quoteInto('e.path LIKE ?', "{$path}%");
        }
        if ($cond) {
            $this->getSelect()->where(join(' OR ', $cond));
        }
        return $this;
    }

    /**
     * Add category level filter
     *
     * @param int|string $level
     * @return $this
     */
    public function addLevelFilter($level)
    {
        $this->addFieldToFilter('level', ['lteq' => $level]);
        return $this;
    }

    /**
     * Add root category filter
     *
     * @return $this
     */
    public function addRootLevelFilter()
    {
        $this->addFieldToFilter('path', ['neq' => '1']);
        $this->addLevelFilter(1);
        return $this;
    }

    /**
     * Add navigation max depth filter
     *
     * @return $this
     * @since 103.0.0
     */
    public function addNavigationMaxDepthFilter()
    {
        $navigationMaxDepth = (int)$this->scopeConfig->getValue(
            'catalog/navigation/max_depth',
            ScopeInterface::SCOPE_STORE
        );
        if ($navigationMaxDepth > 0) {
            $this->addLevelFilter($navigationMaxDepth);
        }
        return $this;
    }

    /**
     * Add order field
     *
     * @param string $field
     * @return $this
     */
    public function addOrderField($field)
    {
        $this->setOrder($field, self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Getter for _productWebsiteTable
     *
     * @return string
     */
    public function getProductWebsiteTable()
    {
        if (empty($this->_productWebsiteTable)) {
            $this->_productWebsiteTable = $this->getTable('catalog_product_website');
        }
        return $this->_productWebsiteTable;
    }

    /**
     * Getter for _productTable
     *
     * @return string
     */
    public function getProductTable()
    {
        if (empty($this->_productTable)) {
            $this->_productTable = $this->getTable('catalog_category_product');
        }
        return $this->_productTable;
    }

    /**
     * Get products count using catalog_category_entity table
     *
     * @param Category $item
     * @param string $websiteId
     * @return int
     */
    private function getProductsCountFromCategoryTable(Category $item, string $websiteId): int
    {
        $productCount = 0;

        if ($item->getAllChildren()) {
            $bind = ['entity_id' => $item->getId(), 'c_path' => $item->getPath() . '/%'];
            $select = $this->_conn->select();
            $select->from(
                ['main_table' => $this->getProductTable()],
                new \Zend_Db_Expr('COUNT(DISTINCT main_table.product_id)')
            )->joinInner(
                ['e' => $this->getTable('catalog_category_entity')],
                'main_table.category_id=e.entity_id',
                []
            )->where(
                '(e.entity_id = :entity_id OR e.path LIKE :c_path)'
            );
            if ($websiteId) {
                $select->join(
                    ['w' => $this->getProductWebsiteTable()],
                    'main_table.product_id = w.product_id',
                    []
                )->where(
                    'w.website_id = ?',
                    $websiteId
                );
            }
            $productCount = (int)$this->_conn->fetchOne($select, $bind);
        }
        return $productCount;
    }

    /**
     * Get query for retrieve count of products per category
     *
     * @param array $categoryIds
     * @param bool $addVisibilityFilter
     * @return Select
     */
    private function getProductsCountQuery(array $categoryIds, $addVisibilityFilter = true): Select
    {
        $categoryTable = $this->_resource->getTableName('catalog_category_product_index');
        $select = $this->_conn->select()
            ->from(
                ['cat_index' => $categoryTable],
                ['category_id' => 'cat_index.category_id', 'count' => 'count(cat_index.product_id)']
            )
            ->where('cat_index.category_id in (?)', \array_map('\intval', $categoryIds));
        if (true === $addVisibilityFilter) {
            $select->where('cat_index.visibility in (?)', $this->catalogProductVisibility->getVisibleInSiteIds());
        }
        if (count($categoryIds) > 1) {
            $select->group('cat_index.category_id');
        }

        return $select;
    }
}
