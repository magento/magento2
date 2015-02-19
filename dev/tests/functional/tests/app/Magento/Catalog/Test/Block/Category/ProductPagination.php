<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Category;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;

/**
 * Pagination page product list.
 */
class ProductPagination extends Block
{
    /**
     * Selector next active element.
     *
     * @var string
     */
    protected $nextPageSelector = '.item.current + .item a';

    /**
     * Getting the active element to go to the next page.
     *
     * @return ElementInterface|null
     */
    public function getNextPage()
    {
        $nextPageItem = $this->_rootElement->find($this->nextPageSelector);
        if ($nextPageItem->isVisible()) {
            return $nextPageItem;
        } else {
            return null;
        }
    }
}
