<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Mtf\Block\Block;

/**
 * Class BottomToolbar
 * Bottom toolbar the product list page
 */
class BottomToolbar extends Block
{
    /**
     * Selector next active element
     *
     * @var string
     */
    protected $nextPageSelector = '.item.current + .item a';

    /**
     * Go to the next page
     *
     * @return bool
     */
    public function nextPage()
    {
        $nextPageItem = $this->_rootElement->find($this->nextPageSelector);

        if ($nextPageItem->isVisible()) {
            $nextPageItem->click();
            return true;
        }
        return false;
    }
}
