<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Model\Product\Type\Price;

/**
 * Test helper for Magento\Catalog\Model\Product\Type\Price
 *
 * Extends Price to add custom methods for testing
 */
class PriceTestHelper extends Price
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
     * Get selection final total price for testing
     *
     * @param mixed $product
     * @param mixed $qty
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSelectionFinalTotalPrice($product = null, $qty = null)
    {
        return $this->data['selection_final_total_price'] ?? $this;
    }

    /**
     * Get total prices for testing
     *
     * @param mixed $product
     * @param mixed $which
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getTotalPrices($product = null, $which = null)
    {
        return $this->data['total_prices'] ?? null;
    }

    /**
     * Set total prices for testing
     *
     * @param mixed $value
     * @return self
     */
    public function setTotalPrices($value): self
    {
        $this->data['total_prices'] = $value;
        return $this;
    }
}
