<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Model\ProductRender\PriceInfo;

/**
 * Test helper for ProductRender PriceInfoInterface
 *
 * Extends the existing PriceInfo implementation to add custom methods for testing.
 * Following PHPUnit 12 migration rule: "Always extend existing implementations instead of implementing from scratch"
 */
class PriceInfoTestHelper extends PriceInfo
{
    /**
     * @var array Internal data storage for custom methods
     */
    private array $data = [];

    /**
     * Constructor - skip parent constructor to avoid dependencies
     */
    public function __construct()
    {
        // Skip parent constructor - clean initialization
    }

    /**
     * Custom getPrice method for testing (not in parent class)
     *
     * @return mixed
     */
    public function getPrice()
    {
        return $this->data['price'] ?? null;
    }

    /**
     * Set price for testing (custom method)
     *
     * @param mixed $price
     * @return self
     */
    public function setPrice($price): self
    {
        $this->data['price'] = $price;
        return $this;
    }
}
