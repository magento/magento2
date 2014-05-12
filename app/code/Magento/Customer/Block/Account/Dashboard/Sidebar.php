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
namespace Magento\Customer\Block\Account\Dashboard;

use Magento\Wishlist\Model\Resource\Item\Collection;
use Magento\Wishlist\Model\Wishlist;

/**
 * Account dashboard sidebar
 */
class Sidebar extends \Magento\Framework\View\Element\Template
{
    /**
     * @var int
     */
    protected $_cartItemsCount;

    /**
     * Enter description here...
     *
     * @var Wishlist
     */
    protected $_wishlist;

    /**
     * @var int
     */
    protected $_compareItems;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $_wishListFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory
     */
    protected $_itemsCompareFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Wishlist\Model\WishlistFactory $wishListFactory
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemsCompareFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishListFactory,
        \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemsCompareFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteFactory = $quoteFactory;
        $this->_wishListFactory = $wishListFactory;
        $this->_itemsCompareFactory = $itemsCompareFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * @return string
     */
    public function getShoppingCartUrl()
    {
        return $this->_urlBuilder->getUrl('checkout/cart');
    }

    /**
     * @return int
     */
    public function getCartItemsCount()
    {
        if (!$this->_cartItemsCount) {
            $this->_cartItemsCount = $this->_createQuote()->setId(
                $this->_checkoutSession->getQuote()->getId()
            )->getItemsCollection()->getSize();
        }

        return $this->_cartItemsCount;
    }

    /**
     * @return Collection
     */
    public function getWishlist()
    {
        if (!$this->_wishlist) {
            $this->_wishlist = $this->_createWishList()->loadByCustomerId($this->_customerSession->getId());
            $this->_wishlist->getItemCollection()->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'price'
            )->addAttributeToSelect(
                'small_image'
            )->addAttributeToFilter(
                'store_id',
                array('in' => $this->_wishlist->getSharedStoreIds())
            )->addAttributeToSort(
                'added_at',
                'desc'
            )->setCurPage(
                1
            )->setPageSize(
                3
            )->load();
        }

        return $this->_wishlist->getItemCollection();
    }

    /**
     * @return int
     */
    public function getWishlistCount()
    {
        return $this->getWishlist()->getSize();
    }

    /**
     * @param Wishlist $wishlistItem
     * @return string
     */
    public function getWishlistAddToCartLink($wishlistItem)
    {
        return $this->_urlBuilder->getUrl('wishlist/index/cart', array('item' => $wishlistItem->getId()));
    }

    /**
     * @return int
     */
    public function getCompareItems()
    {
        if (!$this->_compareItems) {
            $this->_compareItems = $this->_createProductCompareCollection()->setStoreId(
                $this->_storeManager->getStore()->getId()
            );
            $this->_compareItems->setCustomerId(
                $this->currentCustomer->getCustomerId()
            );
            $this->_compareItems->setCustomerId($this->_customerSession->getCustomerId());
            $this->_compareItems->addAttributeToSelect('name')->useProductItem()->load();
        }
        return $this->_compareItems;
    }

    /**
     * @return string
     */
    public function getCompareJsObjectName()
    {
        return "dashboardSidebarCompareJsObject";
    }

    /**
     * @return string
     */
    public function getCompareRemoveUrlTemplate()
    {
        return $this->getUrl('catalog/product_compare/remove', array('product' => '#{id}'));
    }

    /**
     * @return string
     */
    public function getCompareAddUrlTemplate()
    {
        return $this->getUrl('catalog/product_compare/add');
    }

    /**
     * @return string
     */
    public function getCompareUrl()
    {
        return $this->getUrl('catalog/product_compare');
    }

    /**
     * @return \Magento\Sales\Model\Quote
     */
    protected function _createQuote()
    {
        return $this->_quoteFactory->create();
    }

    /**
     * @return Wishlist
     */
    protected function _createWishList()
    {
        return $this->_wishListFactory->create();
    }

    /**
     * @return \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection
     */
    protected function _createProductCompareCollection()
    {
        return $this->_itemsCompareFactory->create();
    }
}
