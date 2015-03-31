<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Block\Category;

use Magento\Msrp\Test\Block\Product\View\Map;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Category view block on the category page.
 */
class View extends Block
{
    /**
     * Click for Price link.
     *
     * @var string
     */
    protected $mapLink = './/*[@class="product-item-info" and contains(.,"%s")]//*[contains(@class,"map-show-info")]';

    /**
     * Popup MAP Block.
     *
     * @var string
     */
    protected $mapPopupBlock = '//ancestor::*[@id="map-popup-click-for-price"]/..';

    /**
     * Open MAP block.
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function openMapBlock(FixtureInterface $product)
    {
        $this->_rootElement->find(sprintf($this->mapLink, $product->getName()), Locator::SELECTOR_XPATH)->click();
        $this->waitForElementVisible($this->mapPopupBlock, Locator::SELECTOR_XPATH);
    }

    /**
     * Return MAP block.
     *
     * @return Map
     */
    public function getMapBlock()
    {
        return $this->blockFactory->create(
            'Magento\Msrp\Test\Block\Product\View\Map',
            ['element' => $this->_rootElement->find($this->mapPopupBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
