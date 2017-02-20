<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities;

use Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar;
use Magento\Mtf\Client\Locator;

/**
 * Wishlist block in Customer's Activities sidebar on create order on backend.
 */
class Wishlist extends Sidebar
{
    /**
     * Wish list locator.
     *
     * @var string
     */
    protected $wishlist = '.sidebar-selector';

    /**
     * Wish list items locator.
     *
     * @var string
     */
    protected $wishlistItems = '#sidebar_data_wishlist';

    /**
     * Select wish list in Wish list dropdown.
     *
     * @param string $name
     * @return bool
     */
    public function selectWishlist($name)
    {
        $this->_rootElement->find($this->wishlist, Locator::SELECTOR_CSS, 'select')->setValue($name);
    }

    /**
     * Get last ordered items block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\WishListItems
     */
    public function getWishlistItemsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Create\CustomerActivities\Sidebar\WishListItems::class,
            ['element' => $this->_rootElement->find($this->wishlistItems)]
        );
    }
}
