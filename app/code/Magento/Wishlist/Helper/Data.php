<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Helper;

use Magento\Framework\App\ActionInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;

/**
 * Wishlist Data Helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config key 'Display Wishlist Summary'
     */
    const XML_PATH_WISHLIST_LINK_USE_QTY = 'wishlist/wishlist_link/use_qty';

    /**
     * Config key 'Display Out of Stock Products'
     */
    const XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK = 'cataloginventory/options/show_out_of_stock';

    /**
     * Currently logged in customer
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    protected $_currentCustomer;

    /**
     * Customer Wishlist instance
     *
     * @var \Magento\Wishlist\Model\Wishlist
     * @since 2.0.0
     */
    protected $_wishlist;

    /**
     * Wishlist Product Items Collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     * @since 2.0.0
     */
    protected $_productCollection;

    /**
     * Wishlist Items Collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     * @since 2.0.0
     */
    protected $_wishlistItemCollection;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     * @since 2.0.0
     */
    protected $_wishlistFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     * @since 2.0.0
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Customer\Helper\View
     * @since 2.0.0
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     * @since 2.0.0
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param WishlistProviderInterface $wishlistProvider
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        WishlistProviderInterface $wishlistProvider,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_storeManager = $storeManager;
        $this->_postDataHelper = $postDataHelper;
        $this->_customerViewHelper = $customerViewHelper;
        $this->wishlistProvider = $wishlistProvider;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Retrieve customer login status
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isCustomerLogIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Retrieve logged in customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 2.0.0
     */
    protected function _getCurrentCustomer()
    {
        return $this->getCustomer();
    }

    /**
     * Set current customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     * @since 2.0.0
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $this->_currentCustomer = $customer;
    }

    /**
     * Retrieve current customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @since 2.0.0
     */
    public function getCustomer()
    {
        if (!$this->_currentCustomer && $this->_customerSession->isLoggedIn()) {
            $this->_currentCustomer = $this->_customerSession->getCustomerDataObject();
        }
        return $this->_currentCustomer;
    }

    /**
     * Retrieve wishlist by logged in customer
     *
     * @return \Magento\Wishlist\Model\Wishlist
     * @since 2.0.0
     */
    public function getWishlist()
    {
        if ($this->_wishlist === null) {
            if ($this->_coreRegistry->registry('shared_wishlist')) {
                $this->_wishlist = $this->_coreRegistry->registry('shared_wishlist');
            } else {
                $this->_wishlist = $this->wishlistProvider->getWishlist();
            }
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve wishlist item count (include config settings)
     * Used in top link menu only
     *
     * @return int
     * @since 2.0.0
     */
    public function getItemCount()
    {
        $storedDisplayType = $this->_customerSession->getWishlistDisplayType();
        $currentDisplayType = $this->scopeConfig->getValue(
            self::XML_PATH_WISHLIST_LINK_USE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $storedDisplayOutOfStockProducts = $this->_customerSession->getDisplayOutOfStockProducts();
        $currentDisplayOutOfStockProducts = $this->scopeConfig->getValue(
            self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$this->_customerSession->hasWishlistItemCount() ||
            $currentDisplayType != $storedDisplayType ||
            $this->_customerSession->hasDisplayOutOfStockProducts() ||
            $currentDisplayOutOfStockProducts != $storedDisplayOutOfStockProducts
        ) {
            $this->calculate();
        }

        return $this->_customerSession->getWishlistItemCount();
    }

    /**
     * Create wishlist item collection
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     * @since 2.0.0
     */
    protected function _createWishlistItemCollection()
    {
        return $this->getWishlist()->getItemCollection();
    }

    /**
     * Retrieve wishlist items collection
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     * @since 2.0.0
     */
    public function getWishlistItemCollection()
    {
        if ($this->_wishlistItemCollection === null) {
            $this->_wishlistItemCollection = $this->_createWishlistItemCollection();
        }
        return $this->_wishlistItemCollection;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else {
                if ($product->hasUrlDataObject()) {
                    $storeId = $product->getUrlDataObject()->getStoreId();
                }
            }
        }
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Retrieve params for removing item from wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @param bool $addReferer
     * @return string
     * @since 2.0.0
     */
    public function getRemoveParams($item, $addReferer = false)
    {
        $url = $this->_getUrl('wishlist/index/remove');
        $params = ['item' => $item->getWishlistItemId()];
        if ($addReferer) {
            $params = $this->addRefererToParams($params);
        }
        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve URL for configuring item from wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return string
     * @since 2.0.0
     */
    public function getConfigureUrl($item)
    {
        return $this->_getUrl(
            'wishlist/index/configure',
            [
                'id' => $item->getWishlistItemId(),
                'product_id' => $item->getProductId()
            ]
        );
    }

    /**
     * Retrieve params for adding product to wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @param array $params
     * @return string
     * @since 2.0.0
     */
    public function getAddParams($item, array $params = [])
    {
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $productId = $item->getProductId();
        }

        $url = $this->_getUrlStore($item)->getUrl('wishlist/index/add');
        if ($productId) {
            $params['product'] = $productId;
        }

        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve params for adding product to wishlist
     *
     * @param int $itemId
     *
     * @return string
     * @since 2.0.0
     */
    public function getMoveFromCartParams($itemId)
    {
        $url = $this->_getUrl('wishlist/index/fromcart');
        $params = ['item' => $itemId];
        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve params for updating product in wishlist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     *
     * @return  string|false
     * @since 2.0.0
     */
    public function getUpdateParams($item)
    {
        $itemId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $itemId = $item->getWishlistItemId();
            $productId = $item->getId();
        }
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $itemId = $item->getId();
            $productId = $item->getProduct()->getId();
        }

        $url = $this->_getUrl('wishlist/index/updateItemOptions');
        if ($itemId) {
            $params = ['id' => $itemId, 'product' => $productId, 'qty' => $item->getQty()];
            return $this->_postDataHelper->getPostData($url, $params);
        }

        return false;
    }

    /**
     * Retrieve params for adding item to shopping cart
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return  string
     * @since 2.0.0
     */
    public function getAddToCartUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('wishlist/index/cart', $this->_getCartUrlParameters($item));
    }

    /**
     * Retrieve URL for adding item to shopping cart
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @param bool $addReferer
     * @return string
     * @since 2.0.0
     */
    public function getAddToCartParams($item, $addReferer = false)
    {
        $params = $this->_getCartUrlParameters($item);
        if ($addReferer) {
            $params = $this->addRefererToParams($params);
        }
        return $this->_postDataHelper->getPostData(
            $this->_getUrlStore($item)->getUrl('wishlist/index/cart'),
            $params
        );
    }

    /**
     * Add UENC referer to params
     *
     * @param array $params
     * @return array
     * @since 2.0.0
     */
    public function addRefererToParams(array $params)
    {
        $params[ActionInterface::PARAM_NAME_URL_ENCODED] =
            $this->urlEncoder->encode($this->_getRequest()->getServer('HTTP_REFERER'));
        return $params;
    }

    /**
     * Retrieve URL for adding item to shopping cart from shared wishlist
     *
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return  string
     * @since 2.0.0
     */
    public function getSharedAddToCartUrl($item)
    {
        return $this->_postDataHelper->getPostData(
            $this->_getUrlStore($item)->getUrl('wishlist/shared/cart'),
            $this->_getCartUrlParameters($item)
        );
    }

    /**
     * Retrieve URL for adding All items to shopping cart from shared wishlist
     *
     * @return string
     * @since 2.0.0
     */
    public function getSharedAddAllToCartUrl()
    {
        return $this->_postDataHelper->getPostData(
            $this->_storeManager->getStore()->getUrl('*/*/allcart', ['_current' => true])
        );
    }

    /**
     * @param string|\Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item $item
     * @return array
     * @since 2.0.0
     */
    protected function _getCartUrlParameters($item)
    {
        $params = [
            'item' => is_string($item) ? $item : $item->getWishlistItemId(),
        ];
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $params['qty'] = $item->getQty();
        }
        return $params;
    }

    /**
     * Retrieve customer wishlist url
     *
     * @param int $wishlistId
     * @return string
     * @since 2.0.0
     */
    public function getListUrl($wishlistId = null)
    {
        $params = [];
        if ($wishlistId) {
            $params['wishlist_id'] = $wishlistId;
        }
        return $this->_getUrl('wishlist', $params);
    }

    /**
     * Check is allow wishlist module
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAllow()
    {
        if ($this->_moduleManager->isOutputEnabled($this->_getModuleName()) && $this->scopeConfig->getValue(
            'wishlist/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check is allow wishlist action in shopping cart
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAllowInCart()
    {
        return $this->isAllow() && $this->getCustomer();
    }

    /**
     * Retrieve customer name
     *
     * @return string|void
     * @since 2.0.0
     */
    public function getCustomerName()
    {
        return $this->getCustomer()
            ? $this->_customerViewHelper->getCustomerName($this->getCustomer())
            : null;
    }

    /**
     * Retrieve RSS URL
     *
     * @param int|string|null $wishlistId
     * @return string
     * @since 2.0.0
     */
    public function getRssUrl($wishlistId = null)
    {
        $customer = $this->_getCurrentCustomer();
        if ($customer) {
            $key = $customer->getId() . ',' . $customer->getEmail();
            $params = ['data' => $this->urlEncoder->encode($key), '_secure' => false];
        }
        if ($wishlistId) {
            $params['wishlist_id'] = $wishlistId;
        }
        return $this->_getUrl('wishlist/index/rss', $params);
    }

    /**
     * Retrieve default empty comment message
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function defaultCommentString()
    {
        return __('Comment');
    }

    /**
     * Retrieve default empty comment message
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getDefaultWishlistName()
    {
        return __('Wish List');
    }

    /**
     * Calculate count of wishlist items and put value to customer session.
     * Method called after wishlist modifications and trigger 'wishlist_items_renewed' event.
     * Depends from configuration.
     *
     * @return $this
     * @since 2.0.0
     */
    public function calculate()
    {
        $count = 0;
        if ($this->getCustomer()) {
            $collection = $this->getWishlistItemCollection()->setInStockFilter(true);
            if ($this->scopeConfig->getValue(
                self::XML_PATH_WISHLIST_LINK_USE_QTY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ) {
                $count = $collection->getItemsQty();
            } else {
                $count = $collection->getSize();
            }
            $this->_customerSession->setWishlistDisplayType(
                $this->scopeConfig->getValue(
                    self::XML_PATH_WISHLIST_LINK_USE_QTY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            $this->_customerSession->setDisplayOutOfStockProducts(
                $this->scopeConfig->getValue(
                    self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }
        $this->_customerSession->setWishlistItemCount($count);
        $this->_eventManager->dispatch('wishlist_items_renewed');
        return $this;
    }

    /**
     * Should display item quantities in my wishlist link
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisplayQty()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_WISHLIST_LINK_USE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve URL to item Product
     *
     * @param  \Magento\Wishlist\Model\Item|\Magento\Catalog\Model\Product $item
     * @param  array $additional
     * @return string
     * @since 2.0.0
     */
    public function getProductUrl($item, $additional = [])
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
                $product = $this->productRepository->getById(
                    $config['product_id'],
                    false,
                    $this->_storeManager->getStore()->getStoreId()
                );
            }
        }
        return $product->getUrlModel()->getUrl($product, $additional);
    }
}
