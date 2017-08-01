<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as ModelCategory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Catalog category helper
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 2.0.0
 */
class Category extends AbstractHelper
{
    const XML_PATH_USE_CATEGORY_CANONICAL_TAG = 'catalog/seo/category_canonical_tag';

    const XML_PATH_CATEGORY_ROOT_ID = 'catalog/category/root_id';

    /**
     * Store categories cache
     *
     * @var array
     * @since 2.0.0
     */
    protected $_storeCategories = [];

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     * @since 2.0.0
     */
    protected $_categoryFactory;

    /**
     * Lib data collection factory
     *
     * @var \Magento\Framework\Data\CollectionFactory
     * @since 2.0.0
     */
    protected $_dataCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     * @since 2.0.0
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\CollectionFactory $dataCollectionFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\CollectionFactory $dataCollectionFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    /**
     * Retrieve current store categories
     *
     * @param bool|string $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection or
     * \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     * @since 2.0.0
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        $parent = $this->_storeManager->getStore()->getRootCategoryId();
        $cacheKey = sprintf('%d-%d-%d-%d', $parent, $sorted, $asCollection, $toLoad);
        if (isset($this->_storeCategories[$cacheKey])) {
            return $this->_storeCategories[$cacheKey];
        }

        /**
         * Check if parent node of the store still exists
         */
        $category = $this->_categoryFactory->create();
        /* @var $category ModelCategory */
        if (!$category->checkId($parent)) {
            if ($asCollection) {
                return $this->_dataCollectionFactory->create();
            }
            return [];
        }

        $recursionLevel = max(
            0,
            (int)$this->scopeConfig->getValue(
                'catalog/navigation/max_depth',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $storeCategories = $category->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);

        $this->_storeCategories[$cacheKey] = $storeCategories;
        return $storeCategories;
    }

    /**
     * Retrieve category url
     *
     * @param ModelCategory $category
     * @return string
     * @since 2.0.0
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof ModelCategory) {
            return $category->getUrl();
        }
        return $this->_categoryFactory->create()->setData($category->getData())->getUrl();
    }

    /**
     * Check if a category can be shown
     *
     * @param ModelCategory|int $category
     * @return bool
     * @since 2.0.0
     */
    public function canShow($category)
    {
        if (is_int($category)) {
            try {
                $category = $this->categoryRepository->get($category);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        } else {
            if (!$category->getId()) {
                return false;
            }
        }

        if (!$category->getIsActive()) {
            return false;
        }
        if (!$category->isInRootCategoryList()) {
            return false;
        }

        return true;
    }

    /**
     * Check if <link rel="canonical"> can be used for category
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     * @since 2.0.0
     */
    public function canUseCanonicalTag($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USE_CATEGORY_CANONICAL_TAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
