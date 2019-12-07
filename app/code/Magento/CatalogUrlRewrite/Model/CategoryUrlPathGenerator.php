<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;

/**
 * Class for generation category url_path
 */
class CategoryUrlPathGenerator
{
    /**
     * Minimal category level that can be considered for generate path
     */
    const MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING = 3;

    /**
     * XML path for category url suffix
     */
    const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $categoryUrlSuffix = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Build category URL path
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface|\Magento\Framework\Model\AbstractModel $category
     * @param null|\Magento\Catalog\Api\Data\CategoryInterface|\Magento\Framework\Model\AbstractModel $parentCategory
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlPath($category, $parentCategory = null)
    {
        if (in_array($category->getParentId(), [Category::ROOT_CATEGORY_ID, Category::TREE_ROOT_ID])) {
            return '';
        }
        $path = $category->getUrlPath();
        if ($path !== null && !$category->dataHasChangedFor('url_key') && !$category->dataHasChangedFor('parent_id')) {
            return $path;
        }
        $path = $category->getUrlKey();
        if ($path === false) {
            return $category->getUrlPath();
        }
        if ($this->isNeedToGenerateUrlPathForParent($category)) {
            $parentCategory = $parentCategory === null ?
                $this->categoryRepository->get($category->getParentId(), $category->getStoreId()) : $parentCategory;
            $parentPath = $this->getUrlPath($parentCategory);
            $path = $parentPath === '' ? $path : $parentPath . '/' . $path;
        }
        return $path;
    }

    /**
     * Define whether we should generate URL path for parent
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return bool
     */
    protected function isNeedToGenerateUrlPathForParent($category)
    {
        return $category->isObjectNew() || $category->getLevel() >= self::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING;
    }

    /**
     * Get category url path
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return string
     */
    public function getUrlPathWithSuffix($category, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $category->getStoreId();
        }
        return $this->getUrlPath($category) . $this->getCategoryUrlSuffix($storeId);
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     */
    protected function getCategoryUrlSuffix($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        if (!isset($this->categoryUrlSuffix[$storeId])) {
            $this->categoryUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                self::XML_PATH_CATEGORY_URL_SUFFIX,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->categoryUrlSuffix[$storeId];
    }

    /**
     * Get canonical category url
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getCanonicalUrlPath($category)
    {
        return 'catalog/category/view/id/' . $category->getId();
    }

    /**
     * Generate category url key
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getUrlKey($category)
    {
        $urlKey = $category->getUrlKey();
        return $category->formatUrlKey($urlKey === '' || $urlKey === null ? $category->getName() : $urlKey);
    }
}
