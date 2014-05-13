<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Pricing\Price;

use \Magento\Framework\ObjectManager;
use \Magento\Framework\Pricing\Object\SaleableInterface;

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
