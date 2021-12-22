<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\SaleableInterface;

/**
 * The Price Collection
 *
 * @api
 * @since 100.0.2
 */
class Collection implements \Iterator
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface
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
     * Cached price models
     *
     * @var array
     */
    protected $priceModels;

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
        $this->priceModels = [];
    }

    /**
     * Reset the Collection to the first element
     *
     * @return mixed|void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        return $this->pool->rewind();
    }

    /**
     * Return the current element
     *
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->get($this->key());
    }

    /**
     * Return the key of the current element
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->pool->key();
    }

    /**
     * Move forward to next element
     *
     * @return mixed|void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        return $this->pool->next();
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
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
