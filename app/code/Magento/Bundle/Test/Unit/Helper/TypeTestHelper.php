<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Helper;

use Magento\Bundle\Model\Product\Type;

/**
 * Test helper for Magento\Bundle\Model\Product\Type
 *
 */
class TypeTestHelper extends Type
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
     * Set parent product ID (custom method not in parent)
     *
     * @param int $productId
     * @return self
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setParentProductId($productId): self
    {
        $this->data['parent_product_id'] = $productId;
        return $this;
    }

    /**
     * Add custom option (custom method not in parent)
     *
     * @param string $option
     * @param mixed $value
     * @return self
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addCustomOption($option, $value): self
    {
        $this->data['custom_options'][$option] = $value;
        return $this;
    }

    /**
     * Custom getCustomOptions method for testing
     *
     * @param float $qty
     * @return self
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setCartQty($qty): self
    {
        $this->data['cart_qty'] = $qty;
        return $this;
    }

    /**
     * Custom getCartQty method for testing
     *
     * @return int|null
     */
    public function getSelectionId()
    {
        return $this->data['selection_id'] ?? null;
    }
}
