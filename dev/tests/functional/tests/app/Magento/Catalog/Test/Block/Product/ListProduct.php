<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product;

use Magento\Catalog\Test\Block\Product\ProductList\ProductItem;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Product list block.
 */
class ListProduct extends Block
{
    /**
     * Locator for product item block.
     *
     * @var string
     */
    protected $productItem = './/*[contains(@class,"product-item-link") and normalize-space(text())="%s"]/ancestor::li';

    /**
     * Sorter dropdown selector.
     *
     * @var string
     */
    protected $sorter = '#sorter';

    /**
     * Return product item block.
     *
     * @param FixtureInterface $product
     * @return ProductItem
     */
    public function getProductItem(FixtureInterface $product)
    {
        $locator = sprintf($this->productItem, $product->getName());

        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Product\ProductList\ProductItem',
            ['element' => $this->_rootElement->find($locator, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get all terms used in sort.
     *
     * @return array
     */
    public function getSortByValues()
    {
        return explode("\n", $this->_rootElement->find($this->sorter)->getText());
    }
}
