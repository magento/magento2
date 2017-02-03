<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Magento\Catalog\Test\Block\Product\ProductList\Related\ProductItem as RelatedProductItem;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Related products section on the page.
 */
class Related extends PromotedSection
{
    /**
     * Return product item block.
     *
     * @param FixtureInterface $product
     * @return RelatedProductItem
     */
    public function getProductItem(FixtureInterface $product)
    {
        $locator = sprintf($this->productItemByName, $product->getName());

        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Product\ProductList\Related\ProductItem',
            ['element' => $this->_rootElement->find($locator, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Return list of products.
     *
     * @return RelatedProductItem[]
     */
    public function getProducts()
    {
        $elements = $this->_rootElement->getElements($this->productItem, Locator::SELECTOR_CSS);
        $result = [];

        foreach ($elements as $element) {
            $result[] = $this->blockFactory->create(
                'Magento\Catalog\Test\Block\Product\ProductList\Related\ProductItem',
                ['element' => $element]
            );
        }

        return $result;
    }
}
