<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Model\Product\Type\Simple;

/**
 * Test helper for Magento\Catalog\Model\Product\Type\Simple
 */
class ProductTypeSimpleTestHelper extends Simple
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
     * Get SKU for testing
     *
     * @param $product
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSku($product)
    {
        return $this->data['sku'] ?? 'Simple Product 1';
    }

    /**
     * Set SKU for testing
     *
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): self
    {
        $this->data['sku'] = $sku;
        return $this;
    }

    /**
     * Get links for testing
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->data['links'] ?? [];
    }

    /**
     * Set links for testing
     *
     * @param array $links
     * @return self
     */
    public function setLinks($links): self
    {
        $this->data['links'] = $links;
        return $this;
    }
}
