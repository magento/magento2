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

/**
 * Catalog url model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model;

class Url
{
    /**
     * Number of characters allowed to be in URL path
     *
     * @var int
     */
    const MAX_REQUEST_PATH_LENGTH = 240;

    /**
     * Number of characters allowed to be in URL path
     * after MAX_REQUEST_PATH_LENGTH number of characters
     *
     * @var int
     */
    const ALLOWED_REQUEST_PATH_OVERFLOW = 10;

    /**
     * Resource model
     *
     * @var \Magento\Catalog\Model\Resource\Url
     */
    protected $_resourceModel;

    /**
     * Categories cache for products
     *
     * @var array
     */
    protected $_categories = array();

    /**
     * Store root categories cache
     *
     * @var array
     */
    protected $_rootCategories = array();

    /**
     * Rewrite cache
     *
     * @var array
     */
    protected $_rewrites = array();

    /**
     * Current url rewrite rule
     *
     * @var \Magento\Framework\Object
     */
    protected $_rewrite;

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    protected $_productUrlSuffix = array();

    /**
     * Cache for category rewrite suffix
     *
     * @var array
     */
    protected $_categoryUrlSuffix = array();

    /**
     * Flag to overwrite config settings for Catalog URL rewrites history maintainance
     *
     * @var bool
     */
    protected $_saveRewritesHistory = null;

    /**
     * Singleton of category model for building URL path
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected static $_categoryForUrlPath;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory = null;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Url factory
     *
     * @var \Magento\Catalog\Model\Resource\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $productUrl;

    /**
     * @param Resource\UrlFactory $urlFactory
     * @param CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Product\Url $productUrl
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\UrlFactory $urlFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Helper\Data $catalogData,
        Product\Url $productUrl
    ) {
        $this->_urlFactory = $urlFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogProduct = $catalogProduct;
        $this->_catalogData = $catalogData;
        $this->productUrl = $productUrl;
    }

    /**
     * Adds url_path property for non-root category - to ensure that url path is not empty.
     *
     * Sometimes attribute 'url_path' can be empty, because url_path hasn't been generated yet,
     * in this case category is loaded with empty url_path and we should generate it manually.
     *
     * @param \Magento\Framework\Object $category
     * @return void
     */
    protected function _addCategoryUrlPath($category)
    {
        if (!$category instanceof \Magento\Framework\Object || $category->getUrlPath()) {
            return;
        }

        // This routine is not intended to be used with root categories,
        // but handle 'em gracefully - ensure them to have empty path.
        if ($category->getLevel() <= 1) {
            $category->setUrlPath('');
            return;
        }

        if (self::$_categoryForUrlPath === null) {
            self::$_categoryForUrlPath = $this->_categoryFactory->create();
        }

        // Generate url_path
        $urlPath = self::$_categoryForUrlPath->setData($category->getData())->getUrlPath();
        $category->setUrlPath($urlPath);
    }

    /**
     * Retrieve stores array or store model
     *
     * @param int $storeId
     * @return \Magento\Store\Model\Store|\Magento\Store\Model\Store[]
     */
    public function getStores($storeId = null)
    {
        return $this->getResource()->getStores($storeId);
    }

    /**
     * Retrieve resource model
     *
     * @return \Magento\Catalog\Model\Resource\Url
     */
    public function getResource()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = $this->_urlFactory->create();
        }
        return $this->_resourceModel;
    }

    /**
     * Retrieve Category model singleton
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategoryModel()
    {
        return $this->getResource()->getCategoryModel();
    }

    /**
     * Returns store root category, uses caching for it
     *
     * @param int $storeId
     * @return \Magento\Framework\Object
     */
    public function getStoreRootCategory($storeId)
    {
        if (!array_key_exists($storeId, $this->_rootCategories)) {
            $category = null;
            $store = $this->getStores($storeId);
            if ($store) {
                $rootCategoryId = $store->getRootCategoryId();
                $category = $this->getResource()->getCategory($rootCategoryId, $storeId);
            }
            $this->_rootCategories[$storeId] = $category;
        }
        return $this->_rootCategories[$storeId];
    }

    /**
     * Setter for $_saveRewritesHistory
     * Force Rewrites History save bypass config settings
     *
     * @param bool $flag
     * @return $this
     */
    public function setShouldSaveRewritesHistory($flag)
    {
        $this->_saveRewritesHistory = (bool)$flag;
        return $this;
    }

    /**
     * Indicate whether to save URL Rewrite History or not (create redirects to old URLs)
     *
     * @param int $storeId Store View
     * @return bool
     */
    public function getShouldSaveRewritesHistory($storeId = null)
    {
        if ($this->_saveRewritesHistory !== null) {
            return $this->_saveRewritesHistory;
        }
        return $this->_catalogData->shouldSaveUrlRewritesHistory($storeId);
    }

    /**
     * Refresh all rewrite urls for some store or for all stores
     * Used to make full reindexing of url rewrites
     *
     * @param int $storeId
     * @return $this
     */
    public function refreshRewrites($storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshRewrites($store->getId());
            }
            return $this;
        }

        $this->clearStoreInvalidRewrites($storeId);
        $this->refreshCategoryRewrite($this->getStores($storeId)->getRootCategoryId(), $storeId, false, false);
        $this->refreshProductRewrites($storeId);
        $this->getResource()->clearCategoryProduct($storeId);

        return $this;
    }

    /**
     * Refresh category rewrite
     *
     * @param \Magento\Framework\Object $category
     * @param string $parentPath
     * @param bool $refreshProducts
     * @param bool $changeRequestPath
     * @return $this
     */
    protected function _refreshCategoryRewrites(
        \Magento\Framework\Object $category,
        $parentPath = null,
        $refreshProducts = true,
        $changeRequestPath = false
    ) {
        if ($category->getId() != $this->getStores($category->getStoreId())->getRootCategoryId()) {
            if ($category->getUrlKey() == '') {
                $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
            } else {
                $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
            }

            $idPath = $this->generatePath('id', null, $category);
            $targetPath = $this->generatePath('target', null, $category);
            $requestPath = $this->getCategoryRequestPath($category, $parentPath, $changeRequestPath);

            $rewriteData = array(
                'store_id' => $category->getStoreId(),
                'category_id' => $category->getId(),
                'product_id' => null,
                'id_path' => $idPath,
                'request_path' => $requestPath,
                'target_path' => $targetPath,
                'is_system' => 1
            );

            $this->getResource()->saveRewrite($rewriteData, $this->_rewrite);

            if ($this->getShouldSaveRewritesHistory($category->getStoreId())) {
                $this->_saveRewriteHistory($rewriteData, $this->_rewrite);
            }

            if ($category->getUrlKey() != $urlKey) {
                $category->setUrlKey($urlKey);
                $this->getResource()->saveCategoryAttribute($category, 'url_key');
            }
            if ($category->getUrlPath() != $requestPath) {
                $category->setUrlPath($requestPath);
                $this->getResource()->saveCategoryAttribute($category, 'url_path');
            }
        } else {
            if ($category->getUrlPath() != '') {
                $category->setUrlPath('');
                $this->getResource()->saveCategoryAttribute($category, 'url_path');
            }
        }

        if ($refreshProducts) {
            $this->_refreshCategoryProductRewrites($category);
        }

        foreach ($category->getChilds() as $child) {
            $this->_refreshCategoryRewrites(
                $child,
                $category->getUrlPath() . '/',
                $refreshProducts,
                $changeRequestPath
            );
        }

        return $this;
    }

    /**
     * Refresh product rewrite
     *
     * @param \Magento\Framework\Object $product
     * @param \Magento\Framework\Object $category
     * @return $this
     */
    protected function _refreshProductRewrite(\Magento\Framework\Object $product, \Magento\Framework\Object $category)
    {
        if ($category->getId() == $category->getPath()) {
            return $this;
        }
        if ($product->getUrlKey() == '') {
            $urlKey = $this->productUrl->formatUrlKey($product->getName());
        } else {
            $urlKey = $this->productUrl->formatUrlKey($product->getUrlKey());
        }

        $idPath = $this->generatePath('id', $product, $category);
        $targetPath = $this->generatePath('target', $product, $category);
        $requestPath = $this->getProductRequestPath($product, $category);

        $categoryId = null;
        $updateKeys = true;
        if ($category->getLevel() > 1) {
            $categoryId = $category->getId();
            $updateKeys = false;
        }

        $rewriteData = array(
            'store_id' => $category->getStoreId(),
            'category_id' => $categoryId,
            'product_id' => $product->getId(),
            'id_path' => $idPath,
            'request_path' => $requestPath,
            'target_path' => $targetPath,
            'is_system' => 1
        );

        $this->getResource()->saveRewrite($rewriteData, $this->_rewrite);

        if ($this->getShouldSaveRewritesHistory($category->getStoreId())) {
            $this->_saveRewriteHistory($rewriteData, $this->_rewrite);
        }

        if ($updateKeys && $product->getUrlKey() != $urlKey) {
            $product->setUrlKey($urlKey);
            $this->getResource()->saveProductAttribute($product, 'url_key');
        }
        if ($updateKeys && $product->getUrlPath() != $requestPath) {
            $product->setUrlPath($requestPath);
            $this->getResource()->saveProductAttribute($product, 'url_path');
        }

        return $this;
    }

    /**
     * Refresh products for category
     *
     * @param \Magento\Framework\Object $category
     * @return $this
     */
    protected function _refreshCategoryProductRewrites(\Magento\Framework\Object $category)
    {
        $originalRewrites = $this->_rewrites;
        $process = true;
        $lastEntityId = 0;
        $firstIteration = true;
        while ($process == true) {
            $products = $this->getResource()->getProductsByCategory($category, $lastEntityId);
            if (!$products) {
                if ($firstIteration) {
                    $this->getResource()->deleteCategoryProductStoreRewrites(
                        $category->getId(),
                        array(),
                        $category->getStoreId()
                    );
                }
                $process = false;
                break;
            }

            // Prepare rewrites for generation
            $rootCategory = $this->getStoreRootCategory($category->getStoreId());
            $categoryIds = array($category->getId(), $rootCategory->getId());
            $this->_rewrites = $this->getResource()->prepareRewrites(
                $category->getStoreId(),
                $categoryIds,
                array_keys($products)
            );

            foreach ($products as $product) {
                // Product always must have rewrite in root category
                $this->_refreshProductRewrite($product, $rootCategory);
                $this->_refreshProductRewrite($product, $category);
            }
            $firstIteration = false;
            unset($products);
        }
        $this->_rewrites = $originalRewrites;
        return $this;
    }

    /**
     * Refresh category and children rewrites
     * Called when reindexing all rewrites and as a reaction on category change that affects rewrites
     *
     * @param int $categoryId
     * @param int|null $storeId
     * @param bool $refreshProducts
     * @param bool $changeRequestPath
     * @return $this
     */
    public function refreshCategoryRewrite(
        $categoryId,
        $storeId = null,
        $refreshProducts = true,
        $changeRequestPath = false
    ) {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshCategoryRewrite($categoryId, $store->getId(), $refreshProducts, $changeRequestPath);
            }
            return $this;
        }

        $category = $this->getResource()->getCategory($categoryId, $storeId);
        if (!$category) {
            return $this;
        }

        // Load all childs and refresh all categories
        $category = $this->getResource()->loadCategoryChilds($category);
        $categoryIds = array($category->getId());
        if ($category->getAllChilds()) {
            $categoryIds = array_merge($categoryIds, array_keys($category->getAllChilds()));
        }
        $this->_rewrites = $this->getResource()->prepareRewrites($storeId, $categoryIds);
        $this->_refreshCategoryRewrites($category, null, $refreshProducts, $changeRequestPath);

        unset($category);
        $this->_rewrites = array();

        return $this;
    }

    /**
     * Refresh product rewrite urls for one store or all stores
     * Called as a reaction on product change that affects rewrites
     *
     * @param int $productId
     * @param int|null $storeId
     * @return $this
     */
    public function refreshProductRewrite($productId, $storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->refreshProductRewrite($productId, $store->getId());
            }
            return $this;
        }

        $product = $this->getResource()->getProduct($productId, $storeId);
        if ($product) {
            $store = $this->getStores($storeId);
            $storeRootCategoryId = $store->getRootCategoryId();

            // List of categories the product is assigned to, filtered by being within the store's categories root
            $categories = $this->getResource()->getCategories($product->getCategoryIds(), $storeId);
            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, '', $productId);

            // Add rewrites for all needed categories
            // If product is assigned to any of store's categories -
            // we also should use store root category to create root product url rewrite
            if (!isset($categories[$storeRootCategoryId])) {
                $categories[$storeRootCategoryId] = $this->getResource()->getCategory($storeRootCategoryId, $storeId);
            }

            // Create product url rewrites
            foreach ($categories as $category) {
                $this->_refreshProductRewrite($product, $category);
            }

            // Remove all other product rewrites created earlier for this store - they're invalid now
            $excludeCategoryIds = array_keys($categories);
            $this->getResource()->clearProductRewrites($productId, $storeId, $excludeCategoryIds);

            unset($categories);
            unset($product);
        } else {
            // Product doesn't belong to this store - clear all its url rewrites including root one
            $this->getResource()->clearProductRewrites($productId, $storeId, array());
        }

        return $this;
    }

    /**
     * Refresh all product rewrites for designated store
     *
     * @param int $storeId
     * @return $this
     */
    public function refreshProductRewrites($storeId)
    {
        $this->_categories = array();
        $storeRootCategoryId = $this->getStores($storeId)->getRootCategoryId();
        $storeRootCategoryPath = $this->getStores($storeId)->getRootCategoryPath();
        $this->_categories[$storeRootCategoryId] = $this->getResource()->getCategory($storeRootCategoryId, $storeId);

        $lastEntityId = 0;
        $process = true;

        while ($process == true) {
            $products = $this->getResource()->getProductsByStore($storeId, $lastEntityId);
            if (!$products) {
                $process = false;
                break;
            }

            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, false, array_keys($products));

            $loadCategories = array();
            foreach ($products as $product) {
                foreach ($product->getCategoryIds() as $categoryId) {
                    if (!isset($this->_categories[$categoryId])) {
                        $loadCategories[$categoryId] = $categoryId;
                    }
                }
            }

            if ($loadCategories) {
                foreach ($this->getResource()->getCategories($loadCategories, $storeId) as $category) {
                    $this->_categories[$category->getId()] = $category;
                }
            }

            foreach ($products as $product) {
                $this->_refreshProductRewrite($product, $this->_categories[$storeRootCategoryId]);
                foreach ($product->getCategoryIds() as $categoryId) {
                    if ($categoryId != $storeRootCategoryId && isset($this->_categories[$categoryId])) {
                        if (strpos($this->_categories[$categoryId]['path'], $storeRootCategoryPath . '/') !== 0) {
                            continue;
                        }
                        $this->_refreshProductRewrite($product, $this->_categories[$categoryId]);
                    }
                }
            }

            unset($products);
            $this->_rewrites = array();
        }

        $this->_categories = array();
        return $this;
    }

    /**
     * Deletes old rewrites for store, left from the times when store had some other root category
     *
     * @param int $storeId
     * @return $this
     */
    public function clearStoreInvalidRewrites($storeId = null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStores() as $store) {
                $this->clearStoreInvalidRewrites($store->getId());
            }
            return $this;
        }

        $this->getResource()->clearStoreInvalidRewrites($storeId);
        return $this;
    }

    /**
     * Get requestPath that was not used yet
     *
     * Will try to get unique path by adding -1 -2 etc. between url_key and optional url_suffix
     *
     * @param int $storeId
     * @param string $requestPath
     * @param string $idPath
     * @param string $urlKey
     * @return string
     */
    public function getUnusedPath($storeId, $requestPath, $idPath, $urlKey)
    {
        if (strpos($idPath, 'product') !== false) {
            $suffix = $this->getProductUrlSuffix($storeId);
        } else {
            $suffix = $this->getCategoryUrlSuffix($storeId);
        }
        if (empty($requestPath)) {
            $requestPath = '-';
        } elseif ($requestPath == $suffix) {
            $requestPath = '-' . $suffix;
        }

        /**
         * Validate maximum length of request path
         */
        if (strlen($requestPath) > self::MAX_REQUEST_PATH_LENGTH + self::ALLOWED_REQUEST_PATH_OVERFLOW) {
            $requestPath = substr($requestPath, 0, self::MAX_REQUEST_PATH_LENGTH);
        }

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            if ($this->_rewrites[$idPath]->getRequestPath() == $requestPath) {
                return $requestPath;
            }
        } else {
            $this->_rewrite = null;
        }

        $rewrite = $this->getResource()->getRewriteByRequestPath($requestPath, $storeId);
        if ($rewrite && $rewrite->getId()) {
            if ($rewrite->getIdPath() == $idPath) {
                $this->_rewrite = $rewrite;
                return $requestPath;
            }
            // match request_url {$urlKey}(-12)(.html) pattern
            $match = array();
            $suffix = preg_quote($suffix);
            $quotedUrlKey = preg_quote($urlKey);
            $regularExpression = "#(?P<urlKey>{$quotedUrlKey})(\\-(?P<copyNum>[0-9]+))?(?P<suffix>{$suffix})?\$#i";
            if (!preg_match($regularExpression, $requestPath, $match)) {
                return $this->getUnusedPath($storeId, '-', $idPath, $urlKey);
            }
            $match['urlKey'] = $match['urlKey'] . '-';
            $match['suffix'] = isset($match['suffix']) ? $match['suffix'] : '';

            $lastRequestPath = $this->getResource()->getLastUsedRewriteRequestIncrement(
                $match['urlKey'],
                $match['suffix'],
                $storeId
            );
            if ($lastRequestPath) {
                $match['copyNum'] = $lastRequestPath;
            }
            return $match['urlKey'] . (isset($match['copyNum']) ? $match['copyNum'] + 1 : '1') . $match['suffix'];
        } else {
            return $requestPath;
        }
    }

    /**
     * Retrieve product rewrite sufix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getProductUrlSuffix($storeId)
    {
        return $this->_catalogProduct->getProductUrlSuffix($storeId);
    }

    /**
     * Retrieve category rewrite sufix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryUrlSuffix($storeId)
    {
        return $this->_catalogCategory->getCategoryUrlSuffix($storeId);
    }

    /**
     * Get unique category request path
     *
     * @param \Magento\Framework\Object $category
     * @param string $parentPath
     * @param bool $changeRequestPath
     * @return string
     */
    public function getCategoryRequestPath($category, $parentPath, $changeRequestPath = false)
    {
        $storeId = $category->getStoreId();
        $idPath = $this->generatePath('id', null, $category);
        $categoryUrlSuffix = $this->getCategoryUrlSuffix($storeId);
        $pathSuffix = '(\-[0-9]+)?';
        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();
        }

        if ($category->getUrlKey() == '') {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
        } else {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
        }

        if (null === $parentPath) {
            $parentPath = $this->getResource()->getCategoryParentPath($category);
        } elseif ($parentPath == '/') {
            $parentPath = '';
        }
        $parentPath = $this->_catalogCategory->getCategoryUrlPath($parentPath, true, $storeId);

        $requestPath = $parentPath . $urlKey;
        if ($changeRequestPath) {
            $pathSuffix = '';
        }
        $regexp = '/^' . preg_quote($requestPath, '/') . $pathSuffix . preg_quote($categoryUrlSuffix, '/') . '$/i';
        if (isset($existingRequestPath) && preg_match($regexp, $existingRequestPath)) {
            return $existingRequestPath;
        }

        $fullPath = $requestPath . $categoryUrlSuffix;
        if ($this->_deleteOldTargetPath($fullPath, $idPath, $storeId)) {
            return $requestPath;
        }

        return $this->getUnusedPath($storeId, $fullPath, $this->generatePath('id', null, $category), $urlKey);
    }

    /**
     * Check if current generated request path is one of the old paths
     *
     * @param string $requestPath
     * @param string $idPath
     * @param int $storeId
     * @return bool
     */
    protected function _deleteOldTargetPath($requestPath, $idPath, $storeId)
    {
        $finalOldTargetPath = $this->getResource()->findFinalTargetPath($requestPath, $storeId);
        if ($finalOldTargetPath && $finalOldTargetPath == $idPath) {
            $this->getResource()->deleteRewriteRecord($requestPath, $storeId, true);
            return true;
        }

        return false;
    }

    /**
     * Get unique product request path
     *
     * @param   \Magento\Framework\Object $product
     * @param   \Magento\Framework\Object $category
     * @return  string
     */
    public function getProductRequestPath($product, $category)
    {
        if ($product->getUrlKey() == '') {
            $urlKey = $this->productUrl->formatUrlKey($product->getName());
        } else {
            $urlKey = $this->productUrl->formatUrlKey($product->getUrlKey());
        }
        $storeId = $category->getStoreId();
        $suffix = $this->getProductUrlSuffix($storeId);
        $idPath = $this->generatePath('id', $product, $category);
        /**
         * Prepare product base request path
         */
        if ($category->getLevel() > 1) {
            // To ensure, that category has path either from attribute or generated now
            $this->_addCategoryUrlPath($category);
            $categoryUrl = $this->_catalogCategory->getCategoryUrlPath($category->getUrlPath(), false, $storeId);
            $requestPath = $categoryUrl . '/' . $urlKey;
        } else {
            $requestPath = $urlKey;
        }

        if (strlen($requestPath) > self::MAX_REQUEST_PATH_LENGTH + self::ALLOWED_REQUEST_PATH_OVERFLOW) {
            $requestPath = substr($requestPath, 0, self::MAX_REQUEST_PATH_LENGTH);
        }

        $this->_rewrite = null;
        /**
         * Check $requestPath should be unique
         */
        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();

            $regexp = '/^' . preg_quote($requestPath, '/') . '(\-[0-9]+)?' . preg_quote($suffix, '/') . '$/i';
            if (preg_match($regexp, $existingRequestPath)) {
                return $existingRequestPath;
            }

            $existingRequestPath = preg_replace('/' . preg_quote($suffix, '/') . '$/', '', $existingRequestPath);
            /**
             * Check if existing request past can be used
             */
            if ($product->getUrlKey() == '' && !empty($requestPath) && strpos($existingRequestPath, $requestPath) === 0
            ) {
                $existingRequestPath = preg_replace(
                    '/^' . preg_quote($requestPath, '/') . '/',
                    '',
                    $existingRequestPath
                );
                if (preg_match('#^-([0-9]+)$#i', $existingRequestPath)) {
                    return $this->_rewrites[$idPath]->getRequestPath();
                }
            }

            $fullPath = $requestPath . $suffix;
            if ($this->_deleteOldTargetPath($fullPath, $idPath, $storeId)) {
                return $fullPath;
            }
        }
        /**
         * Check 2 variants: $requestPath and $requestPath . '-' . $productId
         */
        $validatedPath = $this->getResource()->checkRequestPaths(
            array($requestPath . $suffix, $requestPath . '-' . $product->getId() . $suffix),
            $storeId
        );

        if ($validatedPath) {
            return $validatedPath;
        }
        /**
         * Use unique path generator
         */
        return $this->getUnusedPath($storeId, $requestPath . $suffix, $idPath, $urlKey);
    }

    /**
     * Generate either id path, request path or target path for product and/or category
     *
     * For generating id or system path, either product or category is required
     * For generating request path - category is required
     * $parentPath used only for generating category path
     *
     * @param string $type
     * @param \Magento\Framework\Object $product
     * @param \Magento\Framework\Object $category
     * @param string $parentPath
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function generatePath($type = 'target', $product = null, $category = null, $parentPath = null)
    {
        if (!$product && !$category) {
            throw new \Magento\Framework\Model\Exception(__('Please specify either a category or a product, or both.'));
        }

        // generate id_path
        if ('id' === $type) {
            if (!$product) {
                return 'category/' . $category->getId();
            }
            if ($category && $category->getLevel() > 1) {
                return 'product/' . $product->getId() . '/' . $category->getId();
            }
            return 'product/' . $product->getId();
        }

        // generate request_path
        if ('request' === $type) {
            // for category
            if (!$product) {
                if ($category->getUrlKey() == '') {
                    $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
                } else {
                    $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
                }

                $categoryUrlSuffix = $this->getCategoryUrlSuffix($category->getStoreId());
                if (null === $parentPath) {
                    $parentPath = $this->getResource()->getCategoryParentPath($category);
                } elseif ($parentPath == '/') {
                    $parentPath = '';
                }
                $parentPath = $this->_catalogCategory->getCategoryUrlPath($parentPath, true, $category->getStoreId());

                return $this->getUnusedPath(
                    $category->getStoreId(),
                    $parentPath . $urlKey . $categoryUrlSuffix,
                    $this->generatePath('id', null, $category),
                    $urlKey
                );
            }

            // for product & category
            if (!$category) {
                throw new \Magento\Framework\Model\Exception(
                    __('A category object is required for determining the product request path.')
                );
            }

            if ($product->getUrlKey() == '') {
                $urlKey = $this->productUrl->formatUrlKey($product->getName());
            } else {
                $urlKey = $this->productUrl->formatUrlKey($product->getUrlKey());
            }
            $productUrlSuffix = $this->getProductUrlSuffix($category->getStoreId());
            if ($category->getLevel() > 1) {
                // To ensure, that category has url path either from attribute or generated now
                $this->_addCategoryUrlPath($category);
                $categoryUrl = $this->_catalogCategory->getCategoryUrlPath(
                    $category->getUrlPath(),
                    false,
                    $category->getStoreId()
                );
                return $this->getUnusedPath(
                    $category->getStoreId(),
                    $categoryUrl . '/' . $urlKey . $productUrlSuffix,
                    $this->generatePath('id', $product, $category),
                    $urlKey
                );
            }

            // for product only
            return $this->getUnusedPath(
                $category->getStoreId(),
                $urlKey . $productUrlSuffix,
                $this->generatePath('id', $product),
                $urlKey
            );
        }

        // generate target_path
        if (!$product) {
            return 'catalog/category/view/id/' . $category->getId();
        }
        if ($category && $category->getLevel() > 1) {
            return 'catalog/product/view/id/' . $product->getId() . '/category/' . $category->getId();
        }
        return 'catalog/product/view/id/' . $product->getId();
    }

    /**
     * Return unique string based on the time in microseconds.
     *
     * @return string
     */
    public function generateUniqueIdPath()
    {
        return str_replace('.', '_', uniqid(\Magento\Framework\Math\Random::getRandomNumber(), true));
    }

    /**
     * Create Custom URL Rewrite for old product/category URL after url_key changed
     * It will perform permanent redirect from old URL to new URL
     *
     * @param array $rewriteData New rewrite data
     * @param \Magento\Framework\Object $rewrite Rewrite model
     * @return $this
     */
    protected function _saveRewriteHistory($rewriteData, $rewrite)
    {
        if ($rewrite instanceof \Magento\Framework\Object && $rewrite->getId()) {
            $rewriteData['target_path'] = $rewriteData['request_path'];
            $rewriteData['request_path'] = $rewrite->getRequestPath();
            $rewriteData['id_path'] = $this->generateUniqueIdPath();
            $rewriteData['is_system'] = 0;
            $rewriteData['options'] = 'RP';
            // Redirect = Permanent
            $this->getResource()->saveRewriteHistory($rewriteData);
        }

        return $this;
    }
}
