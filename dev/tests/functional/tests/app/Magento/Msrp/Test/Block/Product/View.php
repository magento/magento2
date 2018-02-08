<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Block\Product;

use Magento\Mtf\Client\Locator;

/**
 * Product view block on the product page.
 */
class View extends \Magento\Catalog\Test\Block\Product\View
{
    /**
     * Click for Price link on Product page.
     *
     * @var string
     */
    protected $clickForPrice = '[id*=msrp-popup]';

    /**
     * MAP popup on Product page.
     *
     * @var string
     */
    protected $mapPopupBlock = '//ancestor::*[@id="map-popup-click-for-price"]/..';

    /**
     * Open MAP block on Product View page.
     *
     * @return void
     */
    public function openMapBlock()
    {
        $this->_rootElement->find($this->clickForPrice, Locator::SELECTOR_CSS)->click();
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
            'Magento\Msrp\Test\Block\Product\Map',
            ['element' => $this->_rootElement->find($this->mapPopupBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
