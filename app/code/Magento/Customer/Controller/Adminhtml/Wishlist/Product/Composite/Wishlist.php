<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite;

use Magento\Framework\Model\Exception as CoreException;

/**
 * Catalog composite product configuration controller
 */
class Wishlist extends \Magento\Backend\App\Action
{
    /**
     * Wishlist we're working with.
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist = null;

    /**
     * Wishlist item we're working with.
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlistItem = null;

    /**
     * Loads wishlist and wishlist item.
     *
     * @return $this
     * @throws CoreException
     */
    protected function _initData()
    {
        $wishlistItemId = (int)$this->getRequest()->getParam('id');
        if (!$wishlistItemId) {
            throw new CoreException(__('No wishlist item ID is defined.'));
        }

        /* @var $wishlistItem \Magento\Wishlist\Model\Item */
        $wishlistItem = $this->_objectManager->create('Magento\Wishlist\Model\Item')->loadWithOptions($wishlistItemId);

        if (!$wishlistItem->getWishlistId()) {
            throw new CoreException(__('Please load the wish list item.'));
        }

        $this->_wishlist = $this->_objectManager->create(
            'Magento\Wishlist\Model\Wishlist'
        )->load(
            $wishlistItem->getWishlistId()
        );

        $this->_wishlistItem = $wishlistItem;

        return $this;
    }

    /**
     * Check the permission to Manage Customers
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Customer::manage');
    }
}
