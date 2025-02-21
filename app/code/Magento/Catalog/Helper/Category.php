<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as ModelCategory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;

/**
 * Catalog category helper
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Category extends AbstractHelper implements ResetAfterRequestInterface
{
    public const XML_PATH_USE_CATEGORY_CANONICAL_TAG = 'catalog/seo/category_canonical_tag';

    public const XML_PATH_CATEGORY_ROOT_ID = 'catalog/category/root_id';

    /**
     * Store categories cache
     *
     * @var array
     */
    protected $_storeCategories = [];

    /**
     * Store manager instance
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Category factory instance
     *
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Lib data collection factory
     *
     * @var CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param Context $context
     * @param CategoryFactory $categoryFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $dataCollectionFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager,
        CollectionFactory $dataCollectionFactory,
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
     * @return Collection|CategoryCollection|array
     * @throws NoSuchEntityException
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
                ScopeInterface::SCOPE_STORE
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
     */
    public function canUseCanonicalTag($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USE_CATEGORY_CANONICAL_TAG,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_storeCategories = [];
    }

    /**
     * Retrieve canonical url for the category page
     *
     * @param string $categoryUrl
     * @return string
     */
    public function getCanonicalUrl(string $categoryUrl): string
    {
        $params = $this->_request->getParams();
        if ($params && isset($params['p'])) {
            $categoryUrl = $categoryUrl . '?p=' . $params['p'];
        }
        return $categoryUrl;
    }
}
