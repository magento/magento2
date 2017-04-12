<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block;

/**
 * Wishlist Product Items abstract Block
 */
abstract class AbstractBlock extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Wishlist Product Items Collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_collection;

    /**
     * Store wishlist Model
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Retrieve Wishlist Data Helper
     *
     * @return \Magento\Wishlist\Helper\Data
     */
    protected function _getHelper()
    {
        return $this->_wishlistHelper;
    }

    /**
     * Retrieve Wishlist model
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    protected function _getWishlist()
    {
        return $this->_getHelper()->getWishlist();
    }

    /**
     * Prepare additional conditions to collection
     *
     * @param \Magento\Wishlist\Model\ResourceModel\Item\Collection $collection
     * @return \Magento\Wishlist\Block\Customer\Wishlist
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareCollection($collection)
    {
        return $this;
    }

    /**
     * Create wishlist item collection
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected function _createWishlistItemCollection()
    {
        return $this->_getWishlist()->getItemCollection();
    }

    /**
     * Retrieve Wishlist Product Items collection
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    public function getWishlistItems()
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_createWishlistItemCollection();
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    /**
     * Retrieve wishlist instance
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlistInstance()
    {
        return $this->_getWishlist();
    }

    /**
     * Retrieve params for Removing item from wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     *
     * @return string
     */
    public function getItemRemoveParams($item)
    {
        return $this->_getHelper()->getRemoveParams($item);
    }

    /**
     * Retrieve Add Item to shopping cart params for POST request
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return string
     */
    public function getItemAddToCartParams($item)
    {
        return $this->_getHelper()->getAddToCartParams($item);
    }

    /**
     * Retrieve Add Item to shopping cart URL from shared wishlist
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return string
     */
    public function getSharedItemAddToCartUrl($item)
    {
        return $this->_getHelper()->getSharedAddToCartUrl($item);
    }

    /**
     * Retrieve URL for adding All items to shopping cart from shared wishlist
     *
     * @return string
     */
    public function getSharedAddAllToCartUrl()
    {
        return $this->_getHelper()->getSharedAddAllToCartUrl();
    }

    /**
     * Retrieve params for adding Product to wishlist
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToWishlistParams($product)
    {
        return $this->_getHelper()->getAddParams($product);
    }

    /**
     * Returns item configure url in wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $product
     *
     * @return string
     */
    public function getItemConfigureUrl($product)
    {
        return $this->_getHelper()->getConfigureUrl($product);
    }

    /**
     * Retrieve Escaped Description for Wishlist Item
     *
     * @param \Magento\Catalog\Model\Product $item
     * @return string
     */
    public function getEscapedDescription($item)
    {
        if ($item->getDescription()) {
            return $this->escapeHtml($item->getDescription());
        }
        return '&nbsp;';
    }

    /**
     * Check Wishlist item has description
     *
     * @param \Magento\Catalog\Model\Product $item
     * @return bool
     */
    public function hasDescription($item)
    {
        return trim($item->getDescription()) != '';
    }

    /**
     * Retrieve formated Date
     *
     * @param string $date
     * @return string
     */
    public function getFormatedDate($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Check is the wishlist has a salable product(s)
     *
     * @return bool
     */
    public function isSaleable()
    {
        foreach ($this->getWishlistItems() as $item) {
            if ($item->getProduct()->isSaleable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve wishlist loaded items count
     *
     * @return int
     */
    public function getWishlistItemsCount()
    {
        return $this->_getWishlist()->getItemsCount();
    }

    /**
     * Retrieve Qty from item
     *
     * @param \Magento\Wishlist\Model\Item|\Magento\Catalog\Model\Product $item
     * @return float
     */
    public function getQty($item)
    {
        $qty = $item->getQty() * 1;
        if (!$qty) {
            $qty = 1;
        }
        return $qty;
    }

    /**
     * Check is the wishlist has items
     *
     * @return bool
     */
    public function hasWishlistItems()
    {
        return $this->getWishlistItemsCount() > 0;
    }

    /**
     * Retrieve URL to item Product
     *
     * @param  \Magento\Wishlist\Model\Item|\Magento\Catalog\Model\Product $item
     * @param  array $additional
     * @return string
     */
    public function getProductUrl($item, $additional = [])
    {
        return $this->_getHelper()->getProductUrl($item, $additional);
    }

    /**
     * Product image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getImageUrl($product)
    {
        return $this->_imageHelper->init($product, 'wishlist_small_image')->getUrl();
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string|null
     */
    public function getItemPriceHtml(
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item,
        $priceType = \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $priceRender->setItem($item);
        $arguments += [
            'zone'         => $renderZone,
            'render_block' => $priceRender
        ];
        return $priceRender ? $priceRender->render($priceType, $item->getProduct(), $arguments) : null;
    }
}
