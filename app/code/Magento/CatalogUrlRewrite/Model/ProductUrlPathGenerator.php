<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Model product url path generator
 */
class ProductUrlPathGenerator
{
    public const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    protected array $productUrlSuffix = [];

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var CategoryUrlPathGenerator
     */
    protected CategoryUrlPathGenerator $categoryUrlPathGenerator;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param Product $product
     * @param Category|null $category
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUrlPath(Product $product, Category $category = null): string
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
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareProductDefaultUrlKey(Product $product): string
    {
        $storedProduct = $this->productRepository->getById($product->getId());
        $storedUrlKey = $storedProduct->getUrlKey();
        return $storedUrlKey ?: $product->formatUrlKey($this->generateProductUrlKey($product));
    }

    /**
     * Retrieve Product Url path with suffix
     *
     * @param Product $product
     * @param int $storeId
     * @param Category|null $category
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUrlPathWithSuffix(Product $product, int $storeId, Category $category = null): string
    {
        return $this->getUrlPath($product, $category) . $this->getProductUrlSuffix($storeId);
    }

    /**
     * Get canonical product url path
     *
     * @param Product $product
     * @param Category|null $category
     * @return string
     */
    public function getCanonicalUrlPath(Product $product, Category $category = null): string
    {
        $path =  'catalog/product/view/id/' . $product->getId();
        return $category ? $path . '/category/' . $category->getId() : $path;
    }

    /**
     * Generate product url key based on url_key entered by merchant or product name
     *
     * @param Product $product
     * @return string|null
     */
    public function getUrlKey(Product $product): ?string
    {
        return $this->prepareProductUrlKey($product);
    }

    /**
     * Prepare url key for product
     *
     * @param Product $product
     * @return string
     */
    protected function prepareProductUrlKey(Product $product): string
    {
        $urlKey = $product->getUrlKey();
        $urlKey = trim(strtolower($urlKey));

        if (!$urlKey) {
            return $product->formatUrlKey($this->generateProductUrlKey($product));
        }

        return $product->formatUrlKey($urlKey);
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param int|null $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProductUrlSuffix(int $storeId = null): string
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                self::XML_PATH_PRODUCT_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->productUrlSuffix[$storeId];
    }

    /**
     * Generate product url key based on name/sku or generate randomly
     *
     * @param Product $product
     * @return string
     */
    private function generateProductUrlKey(Product $product): string
    {
        $strToFormat = $product->getName() ?: $product->getSku();
        return $strToFormat ?: hash('sha256', (string)time());
    }
}
