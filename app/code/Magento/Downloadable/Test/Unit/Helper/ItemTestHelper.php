<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Model\Link\Purchased\Item;

/**
 * Test helper for Downloadable Link Purchased Item
 *
 * This helper extends Item and adds custom methods that can be mocked
 */
class ItemTestHelper extends Item
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
     * Get purchased ID
     *
     * @return int|null
     */
    public function getPurchasedId()
    {
        return $this->data['purchased_id'] ?? null;
    }

    /**
     * Get order item ID
     *
     * @return int|null
     */
    public function getOrderItemId()
    {
        return $this->data['order_item_id'] ?? null;
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
     * Get number of downloads bought
     *
     * @return int|null
     */
    public function getNumberOfDownloadsBought()
    {
        return $this->data['number_of_downloads_bought'] ?? null;
    }

    /**
     * Get number of downloads used
     *
     * @return int|null
     */
    public function getNumberOfDownloadsUsed()
    {
        return $this->data['number_of_downloads_used'] ?? null;
    }

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->data['status'] ?? null;
    }

    /**
     * Get link type
     *
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->data['link_type'] ?? null;
    }

    /**
     * Get link URL
     *
     * @return string|null
     */
    public function getLinkUrl()
    {
        return $this->data['link_url'] ?? null;
    }

    /**
     * Get link file
     *
     * @return string|null
     */
    public function getLinkFile()
    {
        return $this->data['link_file'] ?? null;
    }

    /**
     * Set number of downloads bought
     *
     * @param int $number Number of downloads bought
     * @return self
     */
    public function setNumberOfDownloadsBought($number): self
    {
        $this->data['number_of_downloads_bought'] = $number;
        return $this;
    }

    /**
     * Set number of downloads used
     *
     * @param int $count Number of downloads used
     * @return self
     */
    public function setNumberOfDownloadsUsed($count): self
    {
        $this->data['number_of_downloads_used'] = $count;
        return $this;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status): self
    {
        $this->data['status'] = $status;
        return $this;
    }
}
