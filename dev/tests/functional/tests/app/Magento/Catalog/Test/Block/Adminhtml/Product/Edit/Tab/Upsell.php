<?php
/**
 * @spi
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell\Grid as UpsellGrid;
use Mtf\Client\Element;

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
     * @param Element|null $element [optional]
     * @return UpsellGrid
     */
    protected function getRelatedGrid(Element $element = null)
    {
        $element = $element ? $element : $this->_rootElement;
        return $this->blockFactory->create(
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell\Grid',
            ['element' => $element->find($this->crossSellGrid)]
        );
    }
}
