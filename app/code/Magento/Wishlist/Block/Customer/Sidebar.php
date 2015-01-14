<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist sidebar block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer;

use Magento\Customer\Model\Context;

class Sidebar extends \Magento\Wishlist\Block\AbstractBlock implements \Magento\Framework\View\Block\IdentityInterface
{
    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return __('My Wish List');
    }

    /**
     * Add sidebar conditions to collection
     *
     * @param  \Magento\Wishlist\Model\Resource\Item\Collection $collection
     * @return $this
     */
    protected function _prepareCollection($collection)
    {
        $collection->setCurPage(1)->setPageSize(3)->setInStockFilter(true)->setOrder('added_at');

        return $this;
    }

    /**
     * Prepare before to html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getItemCount()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Can Display wishlist
     *
     * @deprecated after 1.6.2.0
     * @return bool
     */
    public function getCanDisplayWishlist()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * Retrieve Wishlist Product Items collection
     *
     * @return \Magento\Wishlist\Model\Resource\Item\Collection
     */
    public function getWishlistItems()
    {
        if (is_null($this->_collection)) {
            $this->_collection = clone $this->_createWishlistItemCollection();
            $this->_collection->clear();
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    /**
     * Return wishlist items count
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->_getHelper()->getItemCount();
    }

    /**
     * Check whether user has items in his wishlist
     *
     * @return bool
     */
    public function hasWishlistItems()
    {
        return $this->getItemCount() > 0;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getItemCount()) {
            foreach ($this->getWishlistItems() as $item) {
                /** @var $item \Magento\Wishlist\Model\Item */
                $identities = array_merge($identities, $item->getProduct()->getIdentities());
            }
        }
        return $identities;
    }
}
