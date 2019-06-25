<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Profiler;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Catalog category
 *
 * @api
 * @method Category setAffectedProductIds(array $productIds)
 * @method array getAffectedProductIds()
 * @method Category setMovedCategoryId(array $productIds)
 * @method int getMovedCategoryId()
 * @method Category setAffectedCategoryIds(array $categoryIds)
 * @method array getAffectedCategoryIds()
 * @method Category setUrlKey(string $urlKey)
 * @method Category setUrlPath(string $urlPath)
 * @method Category getSkipDeleteChildren()
 * @method Category setSkipDeleteChildren(boolean $value)
 * @method Category setChangedProductIds(array $categoryIds) Set products ids that inserted or deleted for category
 * @method array getChangedProductIds() Get products ids that inserted or deleted for category
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Category extends \Magento\Catalog\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface,
    \Magento\Catalog\Api\Data\CategoryInterface,
    \Magento\Catalog\Api\Data\CategoryTreeInterface
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'catalog_category';

    /**#@+
     * Category display modes
     */
    const DM_PRODUCT = 'PRODUCTS';

    const DM_PAGE = 'PAGE';

    const DM_MIXED = 'PRODUCTS_AND_PAGE';
    /**#@-*/

    /**
     * Id of root category
     */
    const ROOT_CATEGORY_ID = 0;

    /**
     * Id of category tree root
     */
    const TREE_ROOT_ID = 1;

    const CACHE_TAG = 'cat_c';

    /**#@-*/
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
     * @var ResourceModel\Category
     * @since 102.0.6
     */
    protected $_resource;

    /**
     * URL rewrite model
     *
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     * @deprecated 102.0.0
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
    protected $_designAttributes = [
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'page_layout',
        'custom_layout_update',
        'custom_apply_to_products',
    ];

    /**
     * Attributes are that part of interface
     *
     * @deprecated 103.0.0
     * @see CategoryInterface::ATTRIBUTES
     * @var array
     */
    protected $interfaceAttributes = CategoryInterface::ATTRIBUTES;

    /**
     * Category tree model
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Store collection factory
     *
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * Category tree factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\TreeFactory
     */
    protected $_categoryTreeFactory;

    /**
     * @var Indexer\Category\Flat\State
     */
    protected $flatState;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Api\MetadataServiceInterface
     */
    protected $metadataService;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryAttributeRepositoryInterface $metadataService
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTreeResource
     * @param \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Config $catalogConfig
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param Indexer\Category\Flat\State $flatState
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryAttributeRepositoryInterface $metadataService,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTreeResource,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Filter\FilterManager $filter,
        Indexer\Category\Flat\State $flatState,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->metadataService = $metadataService;
        $this->_treeModel = $categoryTreeResource;
        $this->_categoryTreeFactory = $categoryTreeFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_url = $url;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->filter = $filter;
        $this->flatState = $flatState;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->urlFinder = $urlFinder;
        $this->indexerRegistry = $indexerRegistry;
        $this->categoryRepository = $categoryRepository;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
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
            $this->_init(\Magento\Catalog\Model\ResourceModel\Category\Flat::class);
            $this->_useFlatResource = true;
        } else {
            $this->_init(\Magento\Catalog\Model\ResourceModel\Category::class);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getCustomAttributesCodes()
    {
        if ($this->customAttributesCodes === null) {
            $this->customAttributesCodes = $this->getEavAttributesCodes($this->metadataService);
            $this->customAttributesCodes = array_diff($this->customAttributesCodes, CategoryInterface::ATTRIBUTES);
        }
        return $this->customAttributesCodes;
    }

    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
    /**
     * Returns model resource
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\ResourceModel\Category
     * @deprecated 102.0.6 because resource models should be used directly
     * @since 102.0.6
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }
    // phpcs:enable

    /**
     * Get flat resource model flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseFlatResource()
    {
        return $this->_useFlatResource;
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
     * @return \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    public function getTreeModel()
    {
        return $this->_categoryTreeFactory->create();
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Tree
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
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function move($parentId, $afterCategoryId)
    {
        /**
         * Validate new parent category id. (category model is used for backward
         * compatibility in event params)
         */
        try {
            $parent = $this->categoryRepository->get($parentId, $this->getStoreId());
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Sorry, but we can\'t find the new parent category you selected.'
                ),
                $e
            );
        }

        if (!$this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, but we can\'t find the new category you selected.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'We can\'t move the category because the parent category name matches the child category name.'
                )
            );
        }

        /**
         * Setting affected category ids for third party engine index refresh
         */
        $this->setMovedCategoryId($this->getId());
        $oldParentId = $this->getParentId();
        $oldParentIds = $this->getParentIds();

        $eventParams = [
            $this->_eventObject => $this,
            'parent' => $parent,
            'category_id' => $this->getId(),
            'prev_parent_id' => $oldParentId,
            'parent_id' => $parentId,
        ];

        $this->_getResource()->beginTransaction();
        try {
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_before', $eventParams);
            $this->getResource()->changeParent($this, $parent, $afterCategoryId);
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_after', $eventParams);
            $this->_getResource()->commit();

            // Set data for indexer
            $this->setAffectedCategoryIds([$this->getId(), $oldParentId, $parentId]);
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        $this->_eventManager->dispatch('category_move', $eventParams);
        if ($this->flatState->isFlatEnabled()) {
            $flatIndexer = $this->indexerRegistry->get(Indexer\Category\Flat\State::INDEXER_ID);
            if (!$flatIndexer->isScheduled()) {
                $flatIndexer->reindexList([$this->getId(), $oldParentId, $parentId]);
            }
        }
        $productIndexer = $this->indexerRegistry->get(Indexer\Category\Product::INDEXER_ID);
        if (!$productIndexer->isScheduled()) {
            $productIndexer->reindexList(array_merge($this->getPathIds(), $oldParentIds));
        }
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->_cacheManager->clean([self::CACHE_TAG]);

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
     * @return \Magento\Framework\Data\Collection\AbstractDb
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
            return [];
        }

        $array = $this->getData('products_position');
        if ($array === null) {
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
            return [];
        }

        $storeIds = $this->getData('store_ids');
        if ($storeIds) {
            return $storeIds;
        }

        if (!$this->getId()) {
            return [];
        }

        $nodes = [];
        foreach ($this->getPathIds() as $id) {
            $nodes[] = $id;
        }

        $storeIds = [];
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
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return (int)$this->_getData('store_id');
        }
        return (int)$this->_storeManager->getStore()->getId();
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
            Profiler::start('REWRITE: ' . __METHOD__, ['group' => 'REWRITE', 'method' => __METHOD__]);
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
        Profiler::start('REGULAR: ' . __METHOD__, ['group' => 'REGULAR', 'method' => __METHOD__]);
        $urlKey = $this->getUrlKey() ? $this->getUrlKey() : $this->formatUrlKey($this->getName());
        $url = $this->getUrlInstance()->getUrl('catalog/category/view', ['s' => $urlKey, 'id' => $this->getId()]);
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
     * Returns image url
     *
     * @param string $attributeCode
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageUrl($attributeCode = 'image')
    {
        $url = false;
        $image = $this->getData($attributeCode);
        if ($image) {
            if (is_string($image)) {
                $store = $this->_storeManager->getStore();

                $isRelativeUrl = substr($image, 0, 1) === '/';

                $mediaBaseUrl = $store->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                );

                if ($isRelativeUrl) {
                    $url = $image;
                } else {
                    $url = $mediaBaseUrl
                        . ltrim(\Magento\Catalog\Model\Category\FileInfo::ENTITY_MEDIA_PATH, '/')
                        . '/'
                        . $image;
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
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
            $this->setData('parent_category', $this->categoryRepository->get($this->getParentId()));
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
        $parentId = $this->getData(self::KEY_PARENT_ID);
        if (isset($parentId)) {
            return $parentId;
        }
        $parentIds = $this->getParentIds();
        return (int) array_pop($parentIds);
    }

    /**
     * Get all parent categories ids
     *
     * @return array
     */
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), [$this->getId()]);
    }

    /**
     * Retrieve dates for custom design (from & to)
     *
     * @return array
     */
    public function getCustomDesignDate()
    {
        $result = [];
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
        $result = [];
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
     * @param boolean $recursive
     * @param boolean $isActive
     * @param boolean $sortByPosition
     * @return string
     */
    public function getChildren($recursive = false, $isActive = true, $sortByPosition = false)
    {
        return implode(',', $this->getResource()->getChildren($this, $recursive, $isActive, $sortByPosition));
    }

    /**
     * Retrieve Stores where isset category Path
     *
     * Return comma separated string
     *
     * @return string
     */
    public function getPathInStore()
    {
        $result = [];
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
     *
     * Result array contain id of current category because it is part of the path
     *
     * @return array
     */
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if ($ids === null) {
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
        return $this->getData(self::KEY_LEVEL);
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
        return $this->_getData(self::KEY_NAME);
    }

    /**
     * Before delete process
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function beforeDelete()
    {
        if ($this->getResource()->isForbiddenToDelete($this->getId())) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t delete root category.'));
        }
        return parent::beforeDelete();
    }

    /**
     * Get user context.
     *
     * @return UserContextInterface
     */
    private function getUserContext(): UserContextInterface
    {
        if (!$this->userContext) {
            $this->userContext = ObjectManager::getInstance()->get(UserContextInterface::class);
        }

        return $this->userContext;
    }

    /**
     * Get authorization service.
     *
     * @return AuthorizationInterface
     */
    private function getAuthorization(): AuthorizationInterface
    {
        if (!$this->authorization) {
            $this->authorization = ObjectManager::getInstance()->get(AuthorizationInterface::class);
        }

        return $this->authorization;
    }

    /**
     * @inheritDoc
     * @since 103.0.2
     */
    public function beforeSave()
    {
        //Validate changing of design.
        $userType = $this->getUserContext()->getUserType();
        if ((
                $userType === UserContextInterface::USER_TYPE_ADMIN
                || $userType === UserContextInterface::USER_TYPE_INTEGRATION
            )
            && !$this->getAuthorization()->isAllowed('Magento_Catalog::edit_category_design')
        ) {
            foreach ($this->_designAttributes as $attributeCode) {
                $this->setData($attributeCode, $value = $this->getOrigData($attributeCode));
                if (!empty($this->_data[self::CUSTOM_ATTRIBUTES])
                    && array_key_exists($attributeCode, $this->_data[self::CUSTOM_ATTRIBUTES])) {
                    //In case custom attribute were used to update the entity.
                    $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode]->setValue($value);
                }
            }
        }

        return parent::beforeSave();
    }

    /**
     * Retrieve anchors above
     *
     * @return array
     */
    public function getAnchorsAbove()
    {
        $anchors = [];
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
        if (!$this->hasData(self::KEY_PRODUCT_COUNT)) {
            $count = $this->_getResource()->getProductCount($this);
            $this->setData(self::KEY_PRODUCT_COUNT, $count);
        }

        return $this->getData(self::KEY_PRODUCT_COUNT);
    }

    /**
     * Retrieve categories by parent
     *
     * @param int $parent
     * @param int $recursionLevel
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategories($parent, $recursionLevel = 0, $sorted = false, $asCollection = false, $toLoad = true)
    {
        $categories = $this->getResource()->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
        return $categories;
    }

    /**
     * Return parent categories of current category
     *
     * @return \Magento\Framework\DataObject[]|\Magento\Catalog\Model\Category[]
     */
    public function getParentCategories()
    {
        return $this->getResource()->getParentCategories($this);
    }

    /**
     * Return children categories of current category
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Catalog\Model\Category[]
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
        $available = $this->getData(self::KEY_AVAILABLE_SORT_BY);
        if (empty($available)) {
            return [];
        }
        if ($available && !is_array($available)) {
            $available = explode(',', $available);
        }
        return $available;
    }

    /**
     * Retrieve Available Product Listing  Sort By
     *
     * Code as key, value - name
     *
     * @return array
     */
    public function getAvailableSortByOptions()
    {
        $availableSortBy = [];
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
     * @return true|array
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
    public function afterSave()
    {
        $result = parent::afterSave();
        $this->_getResource()->addCommitCallback([$this, 'reindex']);
        return $result;
    }

    /**
     * Init indexing process after category save
     *
     * @return void
     */
    public function reindex()
    {
        if ($this->flatState->isFlatEnabled()) {
            $flatIndexer = $this->indexerRegistry->get(Indexer\Category\Flat\State::INDEXER_ID);
            if (!$flatIndexer->isScheduled()) {
                $idsList = [$this->getId()];
                if ($this->dataHasChangedFor('url_key')) {
                    $idsList = array_merge($idsList, explode(',', $this->getAllChildren()));
                }
                $flatIndexer->reindexList($idsList);
            }
        }
        $productIndexer = $this->indexerRegistry->get(Indexer\Category\Product::INDEXER_ID);

        if (!empty($this->getAffectedProductIds())
                || $this->dataHasChangedFor('is_anchor')
                || $this->dataHasChangedFor('is_active')) {
            if (!$productIndexer->isScheduled()) {
                $productIndexer->reindexList($this->getPathIds());
            } else {
                $productIndexer->invalidate();
            }
        }
    }

    /**
     * Init indexing process after category delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDeleteCommit()
    {
        $this->reindex();
        return parent::afterDeleteCommit();
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [
            self::CACHE_TAG . '_' . $this->getId(),
        ];

        if ($this->hasDataChanges()) {
            $identities[] = Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $this->getId();
        }

        if ($this->dataHasChangedFor('is_anchor') || $this->dataHasChangedFor('is_active')) {
            foreach ($this->getPathIds() as $id) {
                $identities[] = Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $id;
            }
        }

        if (!$this->getId() || $this->isDeleted() || $this->dataHasChangedFor(self::KEY_INCLUDE_IN_MENU)) {
            $identities[] = self::CACHE_TAG;
            $identities[] = Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $this->getId();
        }
        return array_unique($identities);
    }

    /**
     * Returns path
     *
     * @codeCoverageIgnoreStart
     * @return string|null
     */
    public function getPath()
    {
        return $this->getData(self::KEY_PATH);
    }

    /**
     * Returns position
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->getData(self::KEY_POSITION);
    }

    /**
     * Returns children count
     *
     * @return int
     */
    public function getChildrenCount()
    {
        return $this->getData('children_count');
    }

    /**
     * Returns created at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * Returns updated at
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * Returns is active
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive()
    {
        return $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * Returns category id
     *
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->getData('category_id');
    }

    /**
     * Returns display mode
     *
     * @return string|null
     */
    public function getDisplayMode()
    {
        return $this->getData('display_mode');
    }

    /**
     * Returns is include in menu
     *
     * @return bool|null
     */
    public function getIncludeInMenu()
    {
        return $this->getData(self::KEY_INCLUDE_IN_MENU);
    }

    /**
     * Returns url key
     *
     * @return string|null
     */
    public function getUrlKey()
    {
        return $this->getData('url_key');
    }

    /**
     * Returns children data
     *
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]|null
     */
    public function getChildrenData()
    {
        return $this->getData(self::KEY_CHILDREN_DATA);
    }

    //@codeCoverageIgnoreEnd

    /**
     * Return Data Object data in array format.
     *
     * @return array
     * @todo refactor with converter for AbstractExtensibleModel
     */
    public function __toArray()
    {
        $data = $this->_data;
        $hasToArray = function ($model) {
            return is_object($model) && method_exists($model, '__toArray') && is_callable([$model, '__toArray']);
        };
        foreach ($data as $key => $value) {
            if ($hasToArray($value)) {
                $data[$key] = $value->__toArray();
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if ($hasToArray($nestedValue)) {
                        $value[$nestedKey] = $nestedValue->__toArray();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Convert Category model into flat array.
     *
     * @return array
     */
    public function toFlatArray()
    {
        $dataArray = $this->__toArray();
        //process custom attributes if present
        if (array_key_exists('custom_attributes', $dataArray) && !empty($dataArray['custom_attributes'])) {
            /** @var \Magento\Framework\Api\AttributeInterface[] $customAttributes */
            $customAttributes = $dataArray['custom_attributes'];
            unset($dataArray['custom_attributes']);
            foreach ($customAttributes as $attributeValue) {
                $dataArray[$attributeValue[\Magento\Framework\Api\AttributeInterface::ATTRIBUTE_CODE]]
                    = $attributeValue[\Magento\Framework\Api\AttributeInterface::VALUE];
            }
        }
        return ConvertArray::toFlatArray($dataArray);
    }

    //@codeCoverageIgnoreStart

    /**
     * Set parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::KEY_PARENT_ID, $parentId);
    }

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * Set category position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * Set category level
     *
     * @param int $level
     * @return $this
     */
    public function setLevel($level)
    {
        return $this->setData(self::KEY_LEVEL, $level);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedAt);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
     * Set path
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        return $this->setData(self::KEY_PATH, $path);
    }

    /**
     * Set available sort by
     *
     * @param string[]|string $availableSortBy
     * @return $this
     */
    public function setAvailableSortBy($availableSortBy)
    {
        return $this->setData(self::KEY_AVAILABLE_SORT_BY, $availableSortBy);
    }

    /**
     * Set include in menu
     *
     * @param bool $includeInMenu
     * @return $this
     */
    public function setIncludeInMenu($includeInMenu)
    {
        return $this->setData(self::KEY_INCLUDE_IN_MENU, $includeInMenu);
    }

    /**
     * Set product count
     *
     * @param int $productCount
     * @return $this
     */
    public function setProductCount($productCount)
    {
        return $this->setData(self::KEY_PRODUCT_COUNT, $productCount);
    }

    /**
     * Set children data
     *
     * @param \Magento\Catalog\Api\Data\CategoryTreeInterface[] $childrenData
     * @return $this
     */
    public function setChildrenData(array $childrenData = null)
    {
        return $this->setData(self::KEY_CHILDREN_DATA, $childrenData);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Catalog\Api\Data\CategoryExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\CategoryExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
