<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Catalog\Test\Fixture\Product;

/**
 * Class Crosssell
 * Crosssell product block on the page
 */
class Crosssell extends Block
{
    /**
     * Link selector
     *
     * @var string
     */
    protected $linkSelector = '.product.name [title="%s"]';

    /**
     * Verify cross-sell item
     *
     * @param FixtureInterface $crosssell
     * @return bool
     */
    public function verifyProductCrosssell(FixtureInterface $crosssell)
    {
        $match = $this->_rootElement->find(sprintf($this->linkSelector, $crosssell->getName()), Locator::SELECTOR_CSS);
        return $match->isVisible();
    }

    /**
     * Click on cross-sell product link
     *
     * @param Product $product
     * @return SimpleElement
     */
    public function clickLink($product)
    {
        $this->_rootElement->find(
            sprintf($this->linkSelector, $product->getName()),
            Locator::SELECTOR_CSS
        )->click();
    }
}
