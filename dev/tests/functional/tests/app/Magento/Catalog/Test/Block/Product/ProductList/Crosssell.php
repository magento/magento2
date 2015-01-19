<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Magento\Catalog\Test\Fixture\Product;
use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

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
     * @return Element
     */
    public function clickLink($product)
    {
        $this->_rootElement->find(
            sprintf($this->linkSelector, $product->getName()),
            Locator::SELECTOR_CSS
        )->click();
    }
}
