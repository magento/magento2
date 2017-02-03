<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell\Grid as UpsellGrid;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Upsell
 * Up-sells Tab
 */
class Upsell extends AbstractRelated
{
    /**
     * Related products type
     *
     * @var string
     */
    protected $relatedType = 'up_sell_products';

    /**
     * Locator for cross sell products grid
     *
     * @var string
     */
    protected $crossSellGrid = '#up_sell_product_grid';

    /**
     * Return related products grid
     *
     * @param SimpleElement|null $element [optional]
     * @return UpsellGrid
     */
    protected function getRelatedGrid(SimpleElement $element = null)
    {
        $element = $element ? $element : $this->_rootElement;

        return $this->blockFactory->create(
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell\Grid',
            ['element' => $element->find($this->crossSellGrid)]
        );
    }
}
