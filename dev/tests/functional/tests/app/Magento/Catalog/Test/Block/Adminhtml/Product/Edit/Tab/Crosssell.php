<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Crosssell\Grid as CrosssellGrid;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Crosssell
 * Cross-sells Tab
 */
class Crosssell extends AbstractRelated
{
    /**
     * Related products type
     *
     * @var string
     */
    protected $relatedType = 'cross_sell_products';

    /**
     * Locator for cross sell products grid
     *
     * @var string
     */
    protected $crossSellGrid = '#cross_sell_product_grid';

    /**
     * Return cross sell products grid
     *
     * @param SimpleElement|null $element [optional]
     * @return CrosssellGrid
     */
    protected function getRelatedGrid(SimpleElement $element = null)
    {
        $element = $element ? $element : $this->_rootElement;
        return $this->blockFactory->create(
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Crosssell\Grid',
            ['element' => $element->find($this->crossSellGrid)]
        );
    }
}
