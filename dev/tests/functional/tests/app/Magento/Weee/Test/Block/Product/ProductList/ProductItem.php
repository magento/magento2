<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Product\ProductList;

use Magento\Weee\Test\Block\Product\Price;

/**
 * Product item block on frontend category view.
 */
class ProductItem extends \Magento\Catalog\Test\Block\Product\ProductList\ProductItem
{
    /**
     * Return price block.
     *
     * @return Price
     */
    public function getPriceBlock()
    {
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Product\Price',
            ['element' => $this->_rootElement->find($this->priceBox)]
        );
    }
}
