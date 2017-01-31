<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite;

use Magento\Framework\Exception\LocalizedException as CoreException;

/**
 * Catalog composite product configuration controller
 */
abstract class Wishlist extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

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
            throw new CoreException(__('Please define Wish List item ID.'));
        }

        /* @var $wishlistItem \Magento\Wishlist\Model\Item */
        $wishlistItem = $this->_objectManager->create('Magento\Wishlist\Model\Item')->loadWithOptions($wishlistItemId);

        if (!$wishlistItem->getWishlistId()) {
            throw new CoreException(__('Please load Wish List item.'));
        }

        $this->_wishlist = $this->_objectManager->create('Magento\Wishlist\Model\Wishlist')
            ->load($wishlistItem->getWishlistId());

        $this->_wishlistItem = $wishlistItem;

        return $this;
    }
}
