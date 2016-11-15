<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Locator for product item link.
     *
     * @var string
     */
    protected $productItemLink = '.product-item-link';

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
            \Magento\Catalog\Test\Block\Product\ProductList\ProductItem::class,
            ['element' => $this->_rootElement->find($locator, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get product names list.
     *
     * @return array
     */
    public function getProductNames()
    {
        $itemLinks = $this->_rootElement->getElements($this->productItemLink);
        $productNames = [];

        foreach ($itemLinks as $itemLink) {
            $productNames[] = trim($itemLink->getText());
        }

        return $productNames;
    }

    /**
     * Get all terms used in sort.
     *
     * @return array
     */
    public function getSortByValues()
    {
        $result = explode("\n", $this->_rootElement->find($this->sorter)->getText());
        foreach ($result as &$resultItem) {
            $resultItem = trim($resultItem);
        }
        return $result;
    }
}
