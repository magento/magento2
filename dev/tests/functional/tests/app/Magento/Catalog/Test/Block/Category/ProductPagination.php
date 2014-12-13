<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Category;

use Mtf\Block\Block;

/**
 * Class ProductPagination
 * Pagination page product list
 *
 * @package Magento\Catalog\Test\Block\Category
 */
class ProductPagination extends Block
{
    /**
     * Selector next active element
     *
     * @var string
     */
    protected $nextPageSelector = '.item.current + .item a';

    /**
     * Getting the active element to go to the next page
     *
     * @return \Mtf\Client\Element|null
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
