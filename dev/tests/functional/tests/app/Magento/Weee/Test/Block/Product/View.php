<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Product;

use Magento\Mtf\Block\Block;
use Magento\Weee\Test\Block\Price;

/**
 * Product view block on the product page.
 */
class View extends Block
{
    /**
     * Price block.
     *
     * @var string
     */
    protected $priceBox = '.price-box';

    /**
     * Return price block.
     *
     * @return Price
     */
    public function getPriceBlock()
    {
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Price',
            ['element' => $this->_rootElement->find($this->priceBox)]
        );
    }
}
