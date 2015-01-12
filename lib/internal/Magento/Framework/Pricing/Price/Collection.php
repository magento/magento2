<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\Object\SaleableInterface;

/**
 * Class Collection
 */
class Collection implements \Iterator
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var \Magento\Framework\Pricing\Object\SaleableInterface
     */
    protected $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Price\Factory
     */
    protected $priceFactory;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var array
     */
    protected $contains;

    /**
     * @var array
     */
    protected $excludes;

    /**
     * Constructor
     *
     * @param SaleableInterface $saleableItem
     * @param Factory $priceFactory
     * @param Pool $pool
     * @param float $quantity
     */
    public function __construct(
        SaleableInterface $saleableItem,
        Factory $priceFactory,
        Pool $pool,
        $quantity
    ) {
        $this->saleableItem = $saleableItem;
        $this->priceFactory = $priceFactory;
        $this->pool = $pool;
        $this->quantity = $quantity;
    }

    /**
     * Reset the Collection to the first element
     *
     * @return mixed|void
     */
    public function rewind()
    {
        return $this->pool->rewind();
    }

    /**
     * Return the current element
     *
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    public function current()
    {
        return $this->get($this->key());
    }

    /**
     * Return the key of the current element
     *
     * @return string
     */
    public function key()
    {
        return $this->pool->key();
    }

    /**
     * Move forward to next element
     *
     * @return mixed|void
     */
    public function next()
    {
        return $this->pool->next();
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->pool->valid();
    }

    /**
     * Returns price model by code
     *
     * @param string $code
     * @return PriceInterface
     */
    public function get($code)
    {
        return $this->priceFactory->create(
            $this->saleableItem,
            $this->pool[$code],
            $this->quantity
        );
    }
}
