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
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;

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

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Magento\Framework\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Catalog\Model\CategoryFactory */
    protected $categoryFactory;

    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Build category URL path
     *
     * @param \Magento\Catalog\Model\Category|\Magento\Framework\Object $category
     * @return string
     */
    public function getUrlPath($category)
    {
        if ($category->getParentId() == Category::TREE_ROOT_ID) {
            return '';
        }
        $path = $category->getUrlPath();
        if ($path !== null && !$category->dataHasChangedFor('url_key') && !$category->dataHasChangedFor('path_ids')) {
            return $path;
        }
        $path = $category->getUrlKey();
        if ($this->isNeedToGenerateUrlPathForParent($category)) {
            $parentPath = $this->getUrlPath($this->categoryFactory->create()->load($category->getParentId()));
            $path = $parentPath === '' ? $path : $parentPath . '/' . $path;
        }
        return $path;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return bool
     */
    protected function isNeedToGenerateUrlPathForParent($category)
    {
        return $category->getParentId() && $category->getLevel() >= self::MINIMAL_CATEGORY_LEVEL_FOR_PROCESSING;
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
    public function generateUrlKey($category)
    {
        $urlKey = $category->getUrlKey();
        return $category->formatUrlKey($urlKey === '' || $urlKey === null ? $category->getName() : $urlKey);
    }
}
