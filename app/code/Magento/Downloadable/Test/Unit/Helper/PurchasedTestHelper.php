<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Model\Link\Purchased;

/**
 * Test helper class for Purchased with custom methods
 */
class PurchasedTestHelper extends Purchased
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
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->data['customer_id'] ?? null;
    }

    /**
     * Get link section title
     *
     * @return string|null
     */
    public function getLinkSectionTitle()
    {
        return $this->data['link_section_title'] ?? null;
    }

    /**
     * Set link section title
     *
     * @param string $value Link section title (e.g., "Download Links")
     * @return self
     */
    public function setLinkSectionTitle($value): self
    {
        $this->data['link_section_title'] = $value;
        return $this;
    }
}
