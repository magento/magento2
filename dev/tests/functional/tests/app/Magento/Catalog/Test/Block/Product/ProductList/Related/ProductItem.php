<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList\Related;

use Magento\Mtf\Client\Locator;

/**
 * Product item block on related products section.
 */
class ProductItem extends \Magento\Catalog\Test\Block\Product\ProductList\ProductItem
{
    /**
     * Trigger for choose related product.
     *
     * @var string
     */
    protected $triggerChoose = "[name='related_products[]']";

    /**
     * Verify that you can choose the related products.
     *
     * @return bool
     */
    public function isSelectable()
    {
        return $this->_rootElement->find($this->triggerChoose)->isVisible();
    }

    /**
     * Choose the related products.
     *
     * @return void
     */
    public function select()
    {
        $this->_rootElement->find($this->triggerChoose, Locator::SELECTOR_CSS, 'checkbox')->setValue('Yes');
    }
}
