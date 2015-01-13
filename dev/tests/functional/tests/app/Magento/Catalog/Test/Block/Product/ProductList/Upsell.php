<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Upsell
 * Upsell product block on the page
 */
class Upsell extends Block
{
    /**
     * Upsell product locator on the page
     *
     * @var string
     */
    protected $upsellProduct = "//div[normalize-space(div//a)='%s']";

    /**
     * Checking upsell product visibility
     *
     * @param string $productName
     * @return bool
     */
    public function isUpsellProductVisible($productName)
    {
        return $this->getProductElement($productName)->isVisible();
    }

    /**
     * Open upsell product
     *
     * @param string $productName
     */
    public function openUpsellProduct($productName)
    {
        $this->getProductElement($productName)->find('.product.name>a')->click();
    }

    /**
     * Get a the product
     *
     * @param string $productName
     * @return Element
     */
    private function getProductElement($productName)
    {
        return $this->_rootElement->find(sprintf($this->upsellProduct, $productName), Locator::SELECTOR_XPATH);
    }
}
