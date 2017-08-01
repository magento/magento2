<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\SaleableInterface;

/**
 * Class Collection
 *
 * @api
 * @since 2.0.0
 */
class Collection implements \Iterator
{
    /**
     * @var Pool
     * @since 2.0.0
     */
    protected $pool;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface
     * @since 2.0.0
     */
    protected $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Price\Factory
     * @since 2.0.0
     */
    protected $priceFactory;

    /**
     * @var float
     * @since 2.0.0
     */
    protected $quantity;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $contains;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $excludes;

    /**
     * Cached price models
     *
     * @var array
     * @since 2.0.0
     */
    protected $priceModels;

    /**
     * Constructor
     *
     * @param SaleableInterface $saleableItem
     * @param Factory $priceFactory
     * @param Pool $pool
     * @param float $quantity
     * @since 2.0.0
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
        $this->priceModels = [];
    }

    /**
     * Reset the Collection to the first element
     *
     * @return mixed|void
     * @since 2.0.0
     */
    public function rewind()
    {
        return $this->pool->rewind();
    }

    /**
     * Return the current element
     *
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     * @since 2.0.0
     */
    public function current()
    {
        return $this->get($this->key());
    }

    /**
     * Return the key of the current element
     *
     * @return string
     * @since 2.0.0
     */
    public function key()
    {
        return $this->pool->key();
    }

    /**
     * Move forward to next element
     *
     * @return mixed|void
     * @since 2.0.0
     */
    public function next()
    {
        return $this->pool->next();
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get($code)
    {
        if (!isset($this->priceModels[$code])) {
            $this->priceModels[$code] = $this->priceFactory->create(
                $this->saleableItem,
                $this->pool[$code],
                $this->quantity
            );
        }
        return $this->priceModels[$code];
    }
}
