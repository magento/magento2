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

namespace Magento\Wishlist\Block;

/**
 * Wishlist Product Items abstract Block
 */
abstract class AbstractBlock extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Wishlist Product Items Collection
     *
     * @var \Magento\Wishlist\Model\Resource\Item\Collection
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
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = array()
    ) {
        $this->httpContext = $httpContext;
        $this->_productFactory = $productFactory;
        parent::__construct(
            $context,
            $data
        );
        $this->_isScopePrivate = true;
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
     * Retrieve Customer Session instance
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getCustomerSession()
    {
        return $this->_customerSession;
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
     * @param \Magento\Wishlist\Model\Resource\Item\Collection $collection
     * @return \Magento\Wishlist\Block\Customer\Wishlist
     */
    protected function _prepareCollection($collection)
    {
        return $this;
    }

    /**
     * Create wishlist item collection
     *
     * @return \Magento\Wishlist\Model\Resource\Item\Collection
     */
    protected function _createWishlistItemCollection()
    {
        return $this->_getWishlist()->getItemCollection();
    }

    /**
     * Retrieve Wishlist Product Items collection
     *
     * @return \Magento\Wishlist\Model\Resource\Item\Collection
     */
    public function getWishlistItems()
    {
        if (is_null($this->_collection)) {
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
     * Retrieve Add Item to shopping cart URL
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return string
     */
    public function getItemAddToCartUrl($item)
    {
        return $this->_getHelper()->getAddToCartUrl($item);
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
        return $this->formatDate($date, \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM);
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
    public function getProductUrl($item, $additional = array())
    {
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $item;
        } else {
            $product = $item->getProduct();
        }
        $buyRequest = $item->getBuyRequest();
        if (is_object($buyRequest)) {
            $config = $buyRequest->getSuperProductConfig();
            if ($config && !empty($config['product_id'])) {
                $product = $this->_productFactory->create()->setStoreId(
                    $this->_storeManager->getStore()->getStoreId()
                )->load(
                    $config['product_id']
                );
            }
        }
        return parent::getProductUrl($product, $additional);
    }

    /**
     * Product image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getImageUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'small_image')->resize($this->getImageSize());
    }

    /**
     * Product image size getter
     *
     * @return int
     */
    public function getImageSize()
    {
        return $this->getVar('product_image_size', 'Magento_Wishlist');
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
