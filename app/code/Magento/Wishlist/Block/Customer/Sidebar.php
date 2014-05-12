<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist sidebar block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer;

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
        return $this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH);
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
        $identities = array();
        if ($this->getItemCount()) {
            foreach ($this->getWishlistItems() as $item) {
                /** @var $item \Magento\Wishlist\Model\Item */
                $identities = array_merge($identities, $item->getProduct()->getIdentities());
            }
        }
        return $identities;
    }
}
