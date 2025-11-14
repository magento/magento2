<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item;

/**
 * Test helper for Quote Item with controllable storeId, product, and options.
 */
class QuoteItemTestHelper extends Item
{
    /** @var int */
    private $storeId;

    /** @var mixed */
    private $product;

    /** @var array<string, mixed> */
    private $options = [];

    /**
     * @param int $storeId
     */
    public function __construct($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param mixed $p
     * @return $this
     */
    public function setProduct($p)
    {
        $this->product = $p;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $code
     * @param mixed $option
     * @return $this
     */
    public function setOption($code, $option)
    {
        $this->options[$code] = $option;
        return $this;
    }

    /**
     * @param string $code
     * @return mixed|null
     */
    public function getOptionByCode($code)
    {
        return $this->options[$code] ?? null;
    }
}
