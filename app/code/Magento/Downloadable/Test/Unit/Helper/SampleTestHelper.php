<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Model\Sample;

/**
 * Test helper for Downloadable Sample
 *
 */
class SampleTestHelper extends Sample
{
    /**
     * @var array Internal data storage
     */
    private $data = [];

    /**
     * Skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Get product ID
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->data['product_id'] ?? null;
    }

    /**
     * Get store title
     *
     * @return string|null
     */
    public function getStoreTitle()
    {
        return $this->data['store_title'] ?? null;
    }

    /**
     * Set product ID
     *
     * @param int $productId
     * @return self
     */
    public function setProductId($productId): self
    {
        $this->data['product_id'] = $productId;
        return $this;
    }

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return self
     */
    public function setStoreId($storeId): self
    {
        $this->data['store_id'] = $storeId;
        return $this;
    }

    /**
     * Set product website IDs
     *
     * @param array $websiteIds
     * @return self
     */
    public function setProductWebsiteIds($websiteIds): self
    {
        $this->data['product_website_ids'] = $websiteIds;
        return $this;
    }

    /**
     * Set number of downloads
     *
     * @param int $downloads
     * @return self
     */
    public function setNumberOfDownloads($downloads): self
    {
        $this->data['number_of_downloads'] = $downloads;
        return $this;
    }

    /**
     * Set link file
     *
     * @param string $file
     * @return self
     */
    public function setLinkFile($file): self
    {
        $this->data['link_file'] = $file;
        return $this;
    }

    /**
     * Set sample URL
     *
     * @param string $sampleUrl
     * @return $this
     */
    public function setSampleUrl($sampleUrl)
    {
        $this->data['sample_url'] = $sampleUrl;
        return $this;
    }

    /**
     * Get sample file
     *
     * @return string|null
     */
    public function getSampleFile()
    {
        return $this->data['sample_file'] ?? null;
    }

    /**
     * Set sample file
     *
     * @param string $sampleFile
     * @return $this
     */
    public function setSampleFile($sampleFile)
    {
        $this->data['sample_file'] = $sampleFile;
        return $this;
    }

    /**
     * Get sample type
     *
     * @return string|null
     */
    public function getSampleType()
    {
        return $this->data['sample_type'] ?? null;
    }

    /**
     * Set sample type
     *
     * @param string $sampleType
     * @return $this
     */
    public function setSampleType($sampleType)
    {
        $this->data['sample_type'] = $sampleType;
        return $this;
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->data['sort_order'] = $sortOrder;
        return $this;
    }

    /**
     * Get base sample path
     *
     * @return string|null
     */
    public function getBaseSamplePath()
    {
        return $this->data['base_sample_path'] ?? null;
    }
}
