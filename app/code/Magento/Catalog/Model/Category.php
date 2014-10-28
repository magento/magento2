<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model;

use Magento\Framework\Profiler;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * Catalog category
 *
 * @method Category setAffectedProductIds(array $productIds)
 * @method array getAffectedProductIds()
 * @method Category setMovedCategoryId(array $productIds)
 * @method int getMovedCategoryId()
 * @method Category setAffectedCategoryIds(array $categoryIds)
 * @method array getAffectedCategoryIds()
 * @method string getUrlKey()
 * @method Category setUrlKey(string $urlKey)
 * @method Category setUrlPath(string $urlPath)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Category extends \Magento\Catalog\Model\AbstractModel implements \Magento\Framework\Object\IdentityInterface
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'catalog_category';

    /**
     * Category display modes
     */
    const DM_PRODUCT = 'PRODUCTS';

    const DM_PAGE = 'PAGE';

    const DM_MIXED = 'PRODUCTS_AND_PAGE';

    const TREE_ROOT_ID = 1;

    const CACHE_TAG = 'catalog_category';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_category';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'category';

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * URL rewrite model
     *
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $_urlRewrite;

    /**
     * Use flat resource model flag
     *
     * @var bool
     */
    protected $_useFlatResource = false;

    /**
     * Category design attributes
     *
     * @var string[]
     */
    protected $_designAttributes = array(
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'page_layout',
        'custom_layout_update',
        'custom_apply_to_products'
    );

    /**
     * Category tree model
     *
     * @var \Magento\Catalog\Model\Resource\Category\Tree
     */
    protected $_treeModel = null;

    /**
     * Core data
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Store collection factory
     *
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Category tree factory
     *
     * @var \Magento\Catalog\Model\Resource\Category\TreeFactory
     */
    protected $_categoryTreeFactory;

    /**
     * @var Indexer\Category\Flat\State
     */
    protected $flatState;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface
     */
    protected $flatIndexer;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface
     */
    protected $productIndexer;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var UrlFinderInterface */
    protected $urlFinder;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Resource\Category\Tree $categoryTreeResource
     * @param Resource\Category\TreeFactory $categoryTreeFactory
     * @param CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param Resource\Product\CollectionFactory $productCollectionFactory
     * @param Config $catalogConfig
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param Indexer\Category\Flat\State $flatState
     * @param \Magento\Indexer\Model\IndexerInterface $flatIndexer
     * @param \Magento\Indexer\Model\IndexerInterface $productIndexer
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Category\Tree $categoryTreeResource,
        \Magento\Catalog\Model\Resource\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Filter\FilterManager $filter,
        Indexer\Category\Flat\State $flatState,
        \Magento\Indexer\Model\IndexerInterface $flatIndexer,
        \Magento\Indexer\Model\IndexerInterface $productIndexer,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_treeModel = $categoryTreeResource;
        $this->_categoryTreeFactory = $categoryTreeFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_url = $url;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->productIndexer = $productIndexer;
        $this->filter = $filter;
        $this->flatState = $flatState;
        $this->flatIndexer = $flatIndexer;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->urlFinder = $urlFinder;
        parent::__construct($context, $registry, $storeManager, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        // If Flat Index enabled then use it but only on frontend
        if ($this->flatState->isAvailable()) {
            $this->_init('Magento\Catalog\Model\Resource\Category\Flat');
            $this->_useFlatResource = true;
        } else {
            $this->_init('Magento\Catalog\Model\Resource\Category');
        }
    }

    /**
     * Get flat resource model flag
     *
     * @return bool
     */
    public function getUseFlatResource()
    {
        return $this->_useFlatResource;
    }

    /**
     * Return flat indexer object
     *
     * @return \Magento\Indexer\Model\IndexerInterface
     */
    protected function getFlatIndexer()
    {
        if (!$this->flatIndexer->getId()) {
            $this->flatIndexer->load(Indexer\Category\Flat\State::INDEXER_ID);
        }
        return $this->flatIndexer;
    }

    /**
     * Return category product indexer object
     *
     * @return \Magento\Indexer\Model\IndexerInterface
     */
    protected function getProductIndexer()
    {
        if (!$this->productIndexer->getId()) {
            $this->productIndexer->load(Indexer\Category\Product::INDEXER_ID);
        }
        return $this->productIndexer;
    }

    /**
     * Retrieve URL instance
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlInstance()
    {
        return $this->_url;
    }

    /**
     * Retrieve category tree model
     *
     * @return \Magento\Catalog\Model\Resource\Category\Tree
     */
    public function getTreeModel()
    {
        return $this->_categoryTreeFactory->create();
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Catalog\Model\Resource\Category\Tree
     */
    public function getTreeModelInstance()
    {
        return $this->_treeModel;
    }

    /**
     * Move category
     *
     * @param  int $parentId new parent category id
     * @param  null|int $afterCategoryId category id after which we have put current category
     * @return $this
     * @throws \Magento\Framework\Model\Exception|\Exception
     */
    public function move($parentId, $afterCategoryId)
    {
        /**
         * Validate new parent category id. (category model is used for backward
         * compatibility in event params)
         */
        $parent = $this->_categoryFactory->create()->setStoreId($this->getStoreId())->load($parentId);

        if (!$parent->getId()) {
            throw new \Magento\Framework\Model\Exception(
                __(
                    'Sorry, but we can\'t move the category because we can\'t find the new parent category you selected.'
                )
            );
        }

        if (!$this->getId()) {
            throw new \Magento\Framework\Model\Exception(
                __('Sorry, but we can\'t move the category because we can\'t find the new category you selected.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            throw new \Magento\Framework\Model\Exception(
                __(
                    'We can\'t perform this category move operation because the parent category matches the child category.'
                )
            );
        }

        /**
         * Setting affected category ids for third party engine index refresh
         */
        $this->setMovedCategoryId($this->getId());
        $oldParentId = $this->getParentId();
        $oldParentIds = $this->getParentIds();

        $eventParams = array(
            $this->_eventObject => $this,
            'parent' => $parent,
            'category_id' => $this->getId(),
            'prev_parent_id' => $oldParentId,
            'parent_id' => $parentId
        );

        $this->_getResource()->beginTransaction();
        try {
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_before', $eventParams);
            $this->getResource()->changeParent($this, $parent, $afterCategoryId);
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_after', $eventParams);
            $this->_getResource()->commit();

            // Set data for indexer
            $this->setAffectedCategoryIds(array($this->getId(), $oldParentId, $parentId));
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        $this->_eventManager->dispatch('category_move', $eventParams);
        if ($this->flatState->isFlatEnabled() && !$this->getFlatIndexer()->isScheduled()) {
            $this->getFlatIndexer()->reindexList(array($this->getId(), $oldParentId, $parentId));
        }
        if (!$this->getProductIndexer()->isScheduled()) {
            $this->getProductIndexer()->reindexList(array_merge($this->getPathIds(), $oldParentIds));
        }
        $this->_cacheManager->clean(array(self::CACHE_TAG));

        return $this;
    }

    /**
     * Retrieve default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * Get category products collection
     *
     * @return \Magento\Framework\Data\Collection\Db
     */
    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create()->setStoreId(
            $this->getStoreId()
        )->addCategoryFilter(
            $this
        );
        return $collection;
    }

    /**
     * Retrieve all customer attributes
     *
     * @param bool $noDesignAttributes
     * @return array
     * @todo Use with Flat Resource
     */
    public function getAttributes($noDesignAttributes = false)
    {
        $result = $this->getResource()->loadAllAttributes($this)->getSortedAttributes();

        if ($noDesignAttributes) {
            foreach ($result as $k => $a) {
                if (in_array($k, $this->_designAttributes)) {
                    unset($result[$k]);
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve array of product id's for category
     *
     * The array returned has the following format:
     * array($productId => $position)
     *
     * @return array
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return array();
        }

        $array = $this->getData('products_position');
        if (is_null($array)) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }

    /**
     * Retrieve array of store ids for category
     *
     * @return array
     */
    public function getStoreIds()
    {
        if ($this->getInitialSetupFlag()) {
            return array();
        }

        $storeIds = $this->getData('store_ids');
        if ($storeIds) {
            return $storeIds;
        }

        if (!$this->getId()) {
            return array();
        }

        $nodes = array();
        foreach ($this->getPathIds() as $id) {
            $nodes[] = $id;
        }

        $storeIds = array();
        $storeCollection = $this->_storeCollectionFactory->create()->loadByCategoryIds($nodes);
        foreach ($storeCollection as $store) {
            $storeIds[$store->getId()] = $store->getId();
        }

        $entityStoreId = $this->getStoreId();
        if (!in_array($entityStoreId, $storeIds)) {
            array_unshift($storeIds, $entityStoreId);
        }
        if (!in_array(0, $storeIds)) {
            array_unshift($storeIds, 0);
        }

        $this->setData('store_ids', $storeIds);
        return $storeIds;
    }

    /**
     * Return store id.
     *
     * If store id is underfined for category return current active store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->_getData('store_id');
        }
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Set store id
     *
     * @param int|string $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if (!is_numeric($storeId)) {
            $storeId = $this->_storeManager->getStore($storeId)->getId();
        }
        $this->setData('store_id', $storeId);
        $this->getResource()->setStoreId($storeId);
        return $this;
    }

    /**
     * Get category url
     *
     * @return string
     */
    public function getUrl()
    {
        $url = $this->_getData('url');
        if ($url === null) {
            Profiler::start('REWRITE: ' . __METHOD__, array('group' => 'REWRITE', 'method' => __METHOD__));
            if ($this->hasData('request_path') && $this->getRequestPath() != '') {
                $this->setData('url', $this->getUrlInstance()->getDirectUrl($this->getRequestPath()));
                Profiler::stop('REWRITE: ' . __METHOD__);
                return $this->getData('url');
            }

            $rewrite = $this->urlFinder->findOneByData([
                UrlRewrite::ENTITY_ID => $this->getId(),
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::STORE_ID => $this->getStoreId(),
            ]);
            if ($rewrite) {
                $this->setData('url', $this->getUrlInstance()->getDirectUrl($rewrite->getRequestPath()));
                Profiler::stop('REWRITE: ' . __METHOD__);
                return $this->getData('url');
            }

            $this->setData('url', $this->getCategoryIdUrl());
            Profiler::stop('REWRITE: ' . __METHOD__);
            return $this->getData('url');
        }
        return $url;
    }

    /**
     * Retrieve category id URL
     *
     * @return string
     */
    public function getCategoryIdUrl()
    {
        Profiler::start('REGULAR: ' . __METHOD__, array('group' => 'REGULAR', 'method' => __METHOD__));
        $urlKey = $this->getUrlKey() ? $this->getUrlKey() : $this->formatUrlKey($this->getName());
        $url = $this->getUrlInstance()->getUrl('catalog/category/view', array('s' => $urlKey, 'id' => $this->getId()));
        Profiler::stop('REGULAR: ' . __METHOD__);
        return $url;
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->filter->translitUrl($str);
    }

    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getImageUrl()
    {
        $url = false;
        $image = $this->getImage();
        if ($image) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'catalog/category/' . $image;
        }
        return $url;
    }

    /**
     * Get parent category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getParentCategory()
    {
        if (!$this->hasData('parent_category')) {
            $this->setData('parent_category', $this->_categoryFactory->create()->load($this->getParentId()));
        }
        return $this->_getData('parent_category');
    }

    /**
     * Get parent category identifier
     *
     * @return int
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        return intval(array_pop($parentIds));
    }

    /**
     * Get all parent categories ids
     *
     * @return array
     */
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), array($this->getId()));
    }

    /**
     * Retrieve dates for custom design (from & to)
     *
     * @return array
     */
    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }

    /**
     * Retrieve design attributes array
     *
     * @return array
     */
    public function getDesignAttributes()
    {
        $result = array();
        foreach ($this->_designAttributes as $attrName) {
            $result[] = $this->_getAttribute($attrName);
        }
        return $result;
    }

    /**
     * Retrieve attribute by code
     *
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private function _getAttribute($attributeCode)
    {
        if (!$this->_useFlatResource) {
            $attribute = $this->getResource()->getAttribute($attributeCode);
        } else {
            $attribute = $this->_catalogConfig->getAttribute(self::ENTITY, $attributeCode);
        }
        return $attribute;
    }

    /**
     * Get all children categories IDs
     *
     * @param boolean $asArray return result as array instead of comma-separated list of IDs
     * @return array|string
     */
    public function getAllChildren($asArray = false)
    {
        $children = $this->getResource()->getAllChildren($this);
        if ($asArray) {
            return $children;
        } else {
            return implode(',', $children);
        }
    }

    /**
     * Retrieve children ids comma separated
     *
     * @return string
     */
    public function getChildren()
    {
        return implode(',', $this->getResource()->getChildren($this, false));
    }

    /**
     * Retrieve Stores where isset category Path
     * Return comma separated string
     *
     * @return string
     */
    public function getPathInStore()
    {
        $result = array();
        $path = array_reverse($this->getPathIds());
        foreach ($path as $itemId) {
            if ($itemId == $this->_storeManager->getStore()->getRootCategoryId()) {
                break;
            }
            $result[] = $itemId;
        }
        return implode(',', $result);
    }

    /**
     * Check category id existing
     *
     * @param   int $id
     * @return  bool
     */
    public function checkId($id)
    {
        return $this->_getResource()->checkId($id);
    }

    /**
     * Get array categories ids which are part of category path
     * Result array contain id of current category because it is part of the path
     *
     * @return array
     */
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    /**
     * Retrieve level
     *
     * @return int
     */
    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData('level');
    }

    /**
     * Verify category ids
     *
     * @param array $ids
     * @return bool
     */
    public function verifyIds(array $ids)
    {
        return $this->getResource()->verifyIds($ids);
    }

    /**
     * Retrieve Is Category has children flag
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->_getResource()->getChildrenAmount($this) > 0;
    }

    /**
     * Retrieve Request Path
     *
     * @return string
     */
    public function getRequestPath()
    {
        return $this->_getData('request_path');
    }

    /**
     * Retrieve Name data wrapper
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * Before delete process
     *
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    protected function _beforeDelete()
    {
        if ($this->getResource()->isForbiddenToDelete($this->getId())) {
            throw new \Magento\Framework\Model\Exception("Can't delete root category.");
        }
        return parent::_beforeDelete();
    }

    /**
     * Retrieve anchors above
     *
     * @return array
     */
    public function getAnchorsAbove()
    {
        $anchors = array();
        $path = $this->getPathIds();

        if (in_array($this->getId(), $path)) {
            unset($path[array_search($this->getId(), $path)]);
        }

        if ($this->_useFlatResource) {
            $anchors = $this->_getResource()->getAnchorsAbove($path, $this->getStoreId());
        } else {
            if (!$this->_registry->registry('_category_is_anchor_attribute')) {
                $model = $this->_getAttribute('is_anchor');
                $this->_registry->register('_category_is_anchor_attribute', $model);
            }

            $isAnchorAttribute = $this->_registry->registry('_category_is_anchor_attribute');
            if ($isAnchorAttribute) {
                $anchors = $this->getResource()->findWhereAttributeIs($path, $isAnchorAttribute, 1);
            }
        }
        return $anchors;
    }

    /**
     * Retrieve count products of category
     *
     * @return int
     */
    public function getProductCount()
    {
        if (!$this->hasProductCount()) {
            $count = $this->_getResource()->getProductCount($this);
            // load product count
            $this->setData('product_count', $count);
        }
        return $this->getData('product_count');
    }

    /**
     * Retrieve categories by parent
     *
     * @param int $parent
     * @param int $recursionLevel
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted = false, $asCollection = false, $toLoad = true)
    {
        $categories = $this->getResource()->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
        return $categories;
    }

    /**
     * Return parent categories of current category
     *
     * @return \Magento\Framework\Object[]|\Magento\Catalog\Model\Category[]
     */
    public function getParentCategories()
    {
        return $this->getResource()->getParentCategories($this);
    }

    /**
     * Return children categories of current category
     *
     * @return \Magento\Catalog\Model\Resource\Category\Collection|\Magento\Catalog\Model\Category[]
     */
    public function getChildrenCategories()
    {
        return $this->getResource()->getChildrenCategories($this);
    }

    /**
     * Return parent category of current category with own custom design settings
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getParentDesignCategory()
    {
        return $this->getResource()->getParentDesignCategory($this);
    }

    /**
     * Check category is in Root Category list
     *
     * @return bool
     */
    public function isInRootCategoryList()
    {
        // TODO there are bugs in resource models' methods, store_id set to model o/andr to resource model are ignored
        return $this->getResource()->isInRootCategoryList($this);
    }

    /**
     * Retrieve Available int Product Listing sort by
     *
     * @return null|array
     */
    public function getAvailableSortBy()
    {
        $available = $this->getData('available_sort_by');
        if (empty($available)) {
            return array();
        }
        if ($available && !is_array($available)) {
            $available = explode(',', $available);
        }
        return $available;
    }

    /**
     * Retrieve Available Product Listing  Sort By
     * code as key, value - name
     *
     * @return array
     */
    public function getAvailableSortByOptions()
    {
        $availableSortBy = array();
        $defaultSortBy = $this->_catalogConfig->getAttributeUsedForSortByArray();
        if ($this->getAvailableSortBy()) {
            foreach ($this->getAvailableSortBy() as $sortBy) {
                if (isset($defaultSortBy[$sortBy])) {
                    $availableSortBy[$sortBy] = $defaultSortBy[$sortBy];
                }
            }
        }

        if (!$availableSortBy) {
            $availableSortBy = $defaultSortBy;
        }

        return $availableSortBy;
    }

    /**
     * Retrieve Product Listing Default Sort By
     *
     * @return string
     */
    public function getDefaultSortBy()
    {
        if (!($sortBy = $this->getData('default_sort_by'))) {
            $sortBy = $this->_catalogConfig->getProductListDefaultSortBy($this->getStoreId());
        }
        $available = $this->getAvailableSortByOptions();
        if (!isset($available[$sortBy])) {
            $sortBy = array_keys($available);
            $sortBy = $sortBy[0];
        }

        return $sortBy;
    }

    /**
     * Validate attribute values
     *
     * @throws \Magento\Eav\Model\Entity\Attribute\Exception
     * @return bool|array
     */
    public function validate()
    {
        return $this->_getResource()->validate($this);
    }

    /**
     * Add reindexCallback
     *
     * @return \Magento\Catalog\Model\Category
     */
    protected function _afterSave()
    {
        $result = parent::_afterSave();
        $this->_getResource()->addCommitCallback(array($this, 'reindex'));
        return $result;
    }

    /**
     * Init indexing process after category save
     *
     * @return void
     */
    public function reindex()
    {
        if ($this->flatState->isFlatEnabled() && !$this->getFlatIndexer()->isScheduled()) {
            $this->getFlatIndexer()->reindexRow($this->getId());
        }
        if (!$this->getProductIndexer()->isScheduled()) {
            $this->getProductIndexer()->reindexList($this->getPathIds());
        }
    }

    /**
     * Init indexing process after category delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _afterDeleteCommit()
    {
        $this->reindex();
        return parent::_afterDeleteCommit();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [
            self::CACHE_TAG . '_' . $this->getId()
        ];
        if ($this->hasDataChanges() || $this->isDeleted()) {
            $identities[] = Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $this->getId();
        }
        return $identities;
    }
}
