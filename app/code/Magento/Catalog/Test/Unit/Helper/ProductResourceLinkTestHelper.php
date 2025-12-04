<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Link;

/**
 * Test helper for Magento\Catalog\Model\ResourceModel\Product\Link
 *
 */
class ProductResourceLinkTestHelper extends Link
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
        // Skip parent constructor to avoid dependencies
    }

    /**
     * Custom getLinkedProductId method for testing
     *
     * @return mixed
     */
    public function getLinkedProductId()
    {
        return $this->data['linked_product_id'] ?? null;
    }

    /**
     * Custom toArray method for testing
     *
     * @param array $keys
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray(array $keys = [])
    {
        return $this->data['array'] ?? [];
    }
}
