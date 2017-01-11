<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Product;

/**
 * Product view block on the product page.
 */
class View extends \Magento\Catalog\Test\Block\Category\View
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
            \Magento\Weee\Test\Block\Product\Price::class,
            ['element' => $this->_rootElement->find($this->priceBox)]
        );
    }
}
