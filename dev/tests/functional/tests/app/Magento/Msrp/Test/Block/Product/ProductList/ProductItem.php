<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Block\Product\ProductList;

use Magento\Msrp\Test\Block\Product\Map;
use Magento\Mtf\Client\Locator;

/**
 * Product item block on frontend category view.
 */
class ProductItem extends \Magento\Catalog\Test\Block\Product\ProductList\ProductItem
{
    /**
     * Click for Price link.
     *
     * @var string
     */
    protected $mapLink = '.map-show-info';

    /**
     * Popup MAP Block.
     *
     * @var string
     */
    protected $mapPopupBlock = '//ancestor::*[@id="map-popup-click-for-price"]/..';

    /**
     * Open MAP block.
     *
     * @return void
     */
    public function openMapBlock()
    {
        $this->_rootElement->find($this->mapLink)->click();
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
            \Magento\Msrp\Test\Block\Product\Map::class,
            ['element' => $this->_rootElement->find($this->mapPopupBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
