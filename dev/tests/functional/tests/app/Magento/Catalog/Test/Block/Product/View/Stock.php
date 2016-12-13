<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\View;

use Magento\Mtf\Block\Block;

/**
 * Product's stock status block.
 */
class Stock extends Block
{
    /**
     * Out of stock status info.
     *
     * @var string
     */
    protected $outOfStock = '.stock.unavailable';

    /**
     * Get product's stock status.
     *
     * @return array
     */
    public function getOutOfStockStatus()
    {
        return $this->_rootElement->find($this->outOfStock);
    }
}
