<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid;

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
            \Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid::class,
            ['element' => $this->_rootElement->find($this->wishlistGrid)]
        );
    }
}
