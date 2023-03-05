<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block;

use IntlDateFormatter;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use Magento\Wishlist\Model\Wishlist;

/**
 * Wishlist Product Items abstract Block
 */
abstract class AbstractBlock extends AbstractProduct
{
    /**
     * Wishlist Product Items Collection
     *
     * @var Collection
     */
    protected $_collection;

    /**
     * Store wishlist Model
     *
     * @var Wishlist
     */
    protected $_wishlist;

    /**
     * @var Context
     */
    protected $httpContext;

    /**
     * @var ConfigInterface
     */
    private $viewConfig;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @param ProductContext $context
     * @param Context $httpContext
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     */
    public function __construct(
        ProductContext $context,
        Context $httpContext,
        array $data = [],
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null
    ) {
        $this->httpContext = $httpContext;
        parent::__construct(
            $context,
            $data
        );
        $this->viewConfig = $config ?? ObjectManager::getInstance()->get(ConfigInterface::class);
        $this->imageUrlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
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
     * @return Wishlist
     */
    protected function _getWishlist()
    {
        return $this->_getHelper()->getWishlist();
    }

    /**
     * Prepare additional conditions to collection
     *
     * @param Collection $collection
     * @return Customer\Wishlist
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareCollection($collection)
    {
        return $this;
    }

    /**
     * Create wishlist item collection
     *
     * @return Collection
     */
    protected function _createWishlistItemCollection()
    {
        return $this->_getWishlist()->getItemCollection();
    }

    /**
     * Retrieve Wishlist Product Items collection
     *
     * @return Collection
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
     * @return Wishlist
     */
    public function getWishlistInstance()
    {
        return $this->_getWishlist();
    }

    /**
     * Retrieve params for Removing item from wishlist
     *
     * @param Product|Item $item
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
     * @param string|Product|Item $item
     * @return string
     */
    public function getItemAddToCartParams($item)
    {
        return $this->_getHelper()->getAddToCartParams($item);
    }

    /**
     * Retrieve Add Item to shopping cart URL from shared wishlist
     *
     * @param string|Product|Item $item
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
     * @param Product $product
     * @return string
     */
    public function getAddToWishlistParams($product)
    {
        return $this->_getHelper()->getAddParams($product);
    }

    /**
     * Returns item configure url in wishlist
     *
     * @param Product|Item $product
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
     * @param Product $item
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
     * @param Product $item
     * @return bool
     */
    public function hasDescription($item)
    {
        return is_string($item->getDescription()) && trim($item->getDescription()) !== '';
    }

    /**
     * Retrieve formatted Date
     *
     * @param string $date
     * @deprecated 101.1.1
     * @return string
     */
    public function getFormatedDate($date)
    {
        return $this->getFormattedDate($date);
    }

    /**
     * Retrieve formatted Date
     *
     * @param string $date
     * @return string
     */
    public function getFormattedDate($date)
    {
        return $this->formatDate($date, IntlDateFormatter::MEDIUM);
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
     * @param Item|Product $item
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
     * @param  Item|Product $item
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
     * @param Product $product
     * @return string
     */
    public function getImageUrl($product)
    {
        $viewImageConfig = $this->viewConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            Image::MEDIA_TYPE_CONFIG_NODE,
            'wishlist_small_image'
        );
        return $this->imageUrlBuilder->getUrl(
            $product->getData($viewImageConfig['type']),
            'wishlist_small_image'
        );
    }

    /**
     * Return HTML block with price
     *
     * @param ItemInterface $item
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string|null
     */
    public function getItemPriceHtml(
        ItemInterface $item,
        $priceType = ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        /** @var Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $priceRender->setItem($item);
        $arguments += [
            'zone'         => $renderZone,
            'render_block' => $priceRender
        ];
        return $priceRender ? $priceRender->render($priceType, $item->getProduct(), $arguments) : null;
    }
}
