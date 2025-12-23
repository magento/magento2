<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Api\Data\ProductExtension;

/**
 * Test helper for Magento\Catalog\Api\Data\ProductExtensionInterface
 *
 * Implements the ProductExtensionInterface to add custom methods for testing
 */
class ProductExtensionTestHelper extends ProductExtension
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // No dependencies needed
    }

    /**
     * Custom getBundleProductOptions method for testing
     *
     * @return mixed
     */
    public function getBundleProductOptions()
    {
        return $this->data['bundle_product_options'] ?? null;
    }

    /**
     * Custom setBundleProductOptions method for testing
     *
     * @param mixed $bundleProductOptions
     * @return self
     */
    public function setBundleProductOptions($bundleProductOptions): self
    {
        $this->data['bundle_product_options'] = $bundleProductOptions;
        return $this;
    }

    /**
     * Set test data for flexible state management
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setTestData(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get test data
     *
     * @param string $key
     * @return mixed
     */
    public function getTestData(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteIds()
    {
        return $this->data['website_ids'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteIds($websiteIds)
    {
        $this->data['website_ids'] = $websiteIds;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategoryLinks()
    {
        return $this->data['category_links'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setCategoryLinks($categoryLinks = null)
    {
        $this->data['category_links'] = $categoryLinks;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockItem()
    {
        return $this->data['stock_item'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setStockItem($stockItem = null)
    {
        $this->data['stock_item'] = $stockItem;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDownloadableProductLinks()
    {
        return $this->data['downloadable_product_links'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setDownloadableProductLinks($downloadableProductLinks = null)
    {
        $this->data['downloadable_product_links'] = $downloadableProductLinks;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDownloadableProductSamples()
    {
        return $this->data['downloadable_product_samples'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setDownloadableProductSamples($downloadableProductSamples = null)
    {
        $this->data['downloadable_product_samples'] = $downloadableProductSamples;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfigurableProductOptions()
    {
        return $this->data['configurable_product_options'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setConfigurableProductOptions($configurableProductOptions = null)
    {
        $this->data['configurable_product_options'] = $configurableProductOptions;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfigurableProductLinks()
    {
        return $this->data['configurable_product_links'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setConfigurableProductLinks($configurableProductLinks = null)
    {
        $this->data['configurable_product_links'] = $configurableProductLinks;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscounts()
    {
        return $this->data['discounts'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setDiscounts($discounts)
    {
        $this->data['discounts'] = $discounts;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getTestStockItem()
    {
        return $this->data['test_stock_item'] ?? null;
    }

    /**
     * @param $testStockItem
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTestStockItem($testStockItem)
    {
        $this->data['test_stock_item'] = $testStockItem;
        return $this;
    }
}
