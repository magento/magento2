<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Product\ProductList;

use Magento\Mtf\Client\Locator;
use Magento\Catalog\Test\Block\Product\ProductList\ProductItem as CatalogProductItem;

/**
 * Product item block on frontend category view.
 */
class ProductItem extends CatalogProductItem
{
    /**
     * Selector for the swatches of the product.
     *
     * @var string
     */
    protected $swatchBlockSelector = '.swatch-attribute-options';

    /**
     * Check swatches visibility.
     *
     * @return bool
     */
    public function isSwatchesBlockVisible()
    {
        return $this->_rootElement->find($this->swatchBlockSelector)->isVisible();
    }
}
