<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Store\Model\Store;

class ProductUrlPathGenerator
{
    const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    protected $productUrlSuffix = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string
     */
    public function getUrlPath($product, $category = null)
    {
        $path = $product->getData('url_path');
        if ($path === null) {
            $path = $product->getUrlKey()
                ? $this->prepareProductUrlKey($product)
                : $this->prepareProductDefaultUrlKey($product);
        }
        return $category === null
            ? $path
            : $this->categoryUrlPathGenerator->getUrlPath($category) . '/' . $path;
    }

    /**
     * Prepare URL Key with stored product data (fallback for "Use Default Value" logic)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function prepareProductDefaultUrlKey(\Magento\Catalog\Model\Product $product)
    {
        $storedProduct = $this->productRepository->getById($product->getId());
        $storedUrlKey = $storedProduct->getUrlKey();
        return $storedUrlKey ?: $product->formatUrlKey($storedProduct->getName());
    }

    /**
     * Retrieve Product Url path with suffix
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    public function getUrlPathWithSuffix($product, $storeId, $category = null)
    {
        return $this->getUrlPath($product, $category) . $this->getProductUrlSuffix($storeId);
    }

    /**
     * Get canonical product url path
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category|null $category
     * @return string
     */
    public function getCanonicalUrlPath($product, $category = null)
    {
        $path =  'catalog/product/view/id/' . $product->getId();
        return $category ? $path . '/category/' . $category->getId() : $path;
    }

    /**
     * Generate product url key based on url_key entered by merchant or product name
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getUrlKey($product)
    {
        return $product->getUrlKey() === false ? false : $this->prepareProductUrlKey($product);
    }

    /**
     * Prepare url key for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function prepareProductUrlKey(\Magento\Catalog\Model\Product $product)
    {
        $urlKey = $product->getUrlKey();
        return $product->formatUrlKey($urlKey === '' || $urlKey === null ? $product->getName() : $urlKey);
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param int $storeId
     * @return string
     */
    protected function getProductUrlSuffix($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                self::XML_PATH_PRODUCT_URL_SUFFIX,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->productUrlSuffix[$storeId];
    }
}
