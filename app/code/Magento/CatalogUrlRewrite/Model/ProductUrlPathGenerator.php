<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CategoryUrlPathGenerator
     */
    protected $catUrlPathGenerator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryUrlPathGenerator $catUrlPathGenerator
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryUrlPathGenerator $catUrlPathGenerator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->catUrlPathGenerator = $catUrlPathGenerator;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param Product $product
     * @param Category|null $category
     *
     * @return string
     */
    public function getUrlPath(Product $product, Category $category = null) : string
    {
        $path = $product->getData('url_path');
        if ($path === null) {
            $path = $product->getUrlKey()
                ? $this->prepareProductUrlKey($product)
                : $this->prepareProductDefaultUrlKey($product);
        }
        return $category === null
            ? $path
            : $this->catUrlPathGenerator->getUrlPath($category) . '/' . $path;
    }

    /**
     * Prepare URL Key with stored product data (fallback for "Use Default Value" logic)
     *
     * @param Product $product
     * @return string
     */
    protected function prepareProductDefaultUrlKey(Product $product) : string
    {
        $storedProduct = $this->productRepository->getById($product->getId());
        $storedUrlKey = $storedProduct->getUrlKey();
        return $storedUrlKey ?: $product->formatUrlKey($storedProduct->getName());
    }

    /**
     * Retrieve Product Url path with suffix
     *
     * @param Product $product
     * @param int|string $storeId
     * @param Category|null $category
     * @return string
     */
    public function getUrlPathWithSuffix(Product $product, $storeId, Category $category = null) : string
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
    public function getCanonicalUrlPath(Product $product, Category $category = null) : string
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
    public function getUrlKey($product)
    {
        $generatedProductUrlKey = $this->prepareProductUrlKey($product);
        return ($product->getUrlKey() === false || empty($generatedProductUrlKey)) ? null : $generatedProductUrlKey;
    }

    /**
     * Prepare url key for product
     *
     * @param Product $product
     * @return string|bool
     */
    protected function prepareProductUrlKey(Product $product)
    {
        $urlKey = $product->getId() ? $product->getUrlKey() : $this->getNewProductUrlKey($product);
        return $product->formatUrlKey($urlKey === '' || $urlKey === null ? $product->getName() : $urlKey);
    }

    /**
     * Get the URL key of the new product
     *
     * @param Product $product
     * @return string
     */
    protected function getNewProductUrlKey(Product $product) : string
    {
        return empty($product->getUrlKey()) ? $this->generateUniqueNewProductUrlKey($product) : $product->getUrlKey();
    }

    /**
     * Generate unique url key for the new product
     *
     * @param Product $product
     * @return string
     */
    protected function generateUniqueNewProductUrlKey(Product $product) : string
    {
        $urlKey = $product->getName();
        $product->setUrlKey($urlKey);
        while (!$this->isUrlKeyUnique($product)) {
            $urlKey = preg_match('/(.*)-(\d+)$/', $urlKey, $matches)
                ? $matches[1] . '-' . ($matches[2] + 1)
                : $urlKey . '-1';
            $product->setUrlKey($urlKey);
        };
        return $urlKey;
    }

    /**
     * Checking, is this URL key unique?
     *
     * @param Product $product
     * @return bool
     */
    protected function isUrlKeyUnique(Product $product) : bool
    {
        $attribute = $product->getResource()->getAttribute(ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY);
        return $attribute->getEntity()->checkAttributeUniqueValue($attribute, $product);
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @param string|int|null $storeId
     * @return string
     */
    protected function getProductUrlSuffix($storeId = null) : string
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
