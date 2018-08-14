<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Shopcart\Product;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Client\Locator;

/**
 * Class Grid
 * Products in Carts Report grid
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Product row selector
     *
     * @var string
     */
    protected $productRow = '//tr[td[contains(@class,"col-name")] and contains(.,"%s")]';

    /**
     * Product price selector
     *
     * @var string
     */
    protected $productPrice =  '//td[contains(@class,"col-price") and contains(.,"%s")]';

    /**
     * Product carts selector
     *
     * @var string
     */
    protected $productCarts =  '//td[contains(@class,"col-carts") and contains(.,"%d")]';

    /**
     * Check that product visible in grid
     *
     * @param CatalogProductSimple $product
     * @param string $carts
     * @return bool
     */
    public function isProductVisible(CatalogProductSimple $product, $carts)
    {
        $result = false;
        $productRowSelector = sprintf($this->productRow, $product->getName());
        $productPrice = sprintf($this->productPrice, $product->getPrice());
        $productRow = $this->_rootElement->find($productRowSelector, Locator::SELECTOR_XPATH);
        if ($productRow->isVisible()) {
            $result = $productRow->find($productPrice, Locator::SELECTOR_XPATH)->isVisible()
                && $productRow->find(sprintf($this->productCarts, $carts), Locator::SELECTOR_XPATH)->isVisible();
        }

        return $result;
    }
}
