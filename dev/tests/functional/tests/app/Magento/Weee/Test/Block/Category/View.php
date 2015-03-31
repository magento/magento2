<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Category;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Weee\Test\Block\Price;

/**
 * Category view block on the category page.
 */
class View extends Block
{
    /**
     * Price block.
     *
     * @var string
     */
    protected $priceBox = './/*[@class="product-item-info" and contains(.,"%s")]//*[contains(@class,"price-box")]';

    /**
     * Return price block.
     *
     * @param FixtureInterface $product
     * @return Price
     */
    public function getPriceBlock(FixtureInterface $product)
    {
        $priceBoxLocator = sprintf($this->priceBox, $product->getName());
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Price',
            ['element' => $this->_rootElement->find($priceBoxLocator, Locator::SELECTOR_XPATH)]
        );
    }
}
