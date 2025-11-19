<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Helper;

use Magento\Wishlist\Model\Item;

/**
 * Test helper for Magento\Catalog\Model\Product\Configuration\Item\ItemInterface
 * Using Magento\Wishlist\Model\Item class as We need an concrete class to implement the interface
 * Implements ItemInterface to provide custom methods for testing
 */
class ItemTestHelper extends Item
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
        // No parent constructor to call for interface
    }

    /**
     * Get option by code
     *
     * @param string $code
     * @return mixed
     */
    public function getOptionByCode($code)
    {
        // Support callback pattern used in Bundle tests
        if (isset($this->data['option_by_code_callback']) && is_callable($this->data['option_by_code_callback'])) {
            return call_user_func($this->data['option_by_code_callback'], $code);
        }

        return $this->data['options'][$code] ?? $this->data['option_by_code_callback'] ?? null;
    }

    /**
     * Set option by code for testing
     * Supports both individual options and callback patterns
     *
     * @param string|callable|null $codeOrCallback
     * @param mixed $option
     * @return self
     */
    public function setOptionByCode($codeOrCallback, $option = null): self
    {
        // If only one parameter is provided, it's either a callback or null
        if (func_num_args() === 1) {
            $this->data['option_by_code_callback'] = $codeOrCallback;
        } else {
            // Two parameters: traditional code => option mapping
            $this->data['options'][$codeOrCallback] = $option;
        }
        return $this;
    }
}
