<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Product item block on frontend category view.
 */
class ProductItem extends Block
{
    /**
     * 'Add to Card' button.
     *
     * @var string
     */
    protected $addToCard = "button.action.tocart";

    /**
     * Checking that "Add to Card" button is visible
     *
     * @return bool
     */
    public function isVisibleAddToCardButton()
    {
        return $this->_rootElement->find($this->addToCard, Locator::SELECTOR_CSS)->isVisible();
    }
}
