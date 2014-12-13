<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid;
use Mtf\Client\Element;

/**
 * Class Wishlist
 * Customer Wishlist edit tab
 */
class Wishlist extends Tab
{
    /**
     * Wishlist grid selector
     *
     * @var string
     */
    protected $wishlistGrid = '#wishlistGrid';

    /**
     * Get wishlist grid
     *
     * @return Grid
     */
    public function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid',
            ['element' => $this->_rootElement->find($this->wishlistGrid)]
        );
    }
}
