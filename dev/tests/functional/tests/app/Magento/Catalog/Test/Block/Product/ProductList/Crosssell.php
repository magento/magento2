<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Cross-sell product block on the checkout page.
 */
class Crosssell extends Block
{
    /**
     * Product item block.
     *
     * @var string
     */
    protected $productItem = 'li.product-item';

    /**
     * Product item block by product name.
     *
     * @var string
     */
    protected $productItemByName = './/*[contains(@class,"product-item-link") and @title="%s"]/ancestor::li';

    /**
     * Check whether block is visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->_rootElement->isVisible();
    }

    /**
     * Return product item block.
     *
     * @param FixtureInterface $product
     * @return ProductItem
     */
    public function getProductItem(FixtureInterface $product)
    {
        $locator = sprintf($this->productItemByName, $product->getName());

        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Product\ProductList\ProductItem',
            ['element' => $this->_rootElement->find($locator, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get list of product names.
     *
     * @return array
     */
    public function getProductNames()
    {
        $productItems = $this->_rootElement->getElements($this->productItem, Locator::SELECTOR_CSS);
        $names = [];

        foreach ($productItems as $productItem) {
            /** @var ProductItem $productItemBlock */
            $productItemBlock = $this->blockFactory->create(
                'Magento\Catalog\Test\Block\Product\ProductList\ProductItem',
                ['element' => $productItem]
            );

            $names[] = $productItemBlock->getProductName();
        }

        return $names;
    }
}
