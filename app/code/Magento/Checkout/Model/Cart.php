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
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart model
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Model;

class Cart extends \Magento\Object implements \Magento\Checkout\Model\Cart\CartInterface
{
    /**
     * Shopping cart items summary quantity(s)
     *
     * @var int|null
     */
    protected $_summaryQty;

    /**
     * List of product ids in shopping cart
     *
     * @var array|null
     */
    protected $_productIds;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Checkout\Model\Resource\Cart
     */
    protected $_resourceCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\Model\Message
     */
    protected $_message;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Resource\Cart $resourceCart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Model\Message $message
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Resource\Cart $resourceCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Model\Message $message,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_resourceCart = $resourceCart;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_message = $message;
        parent::__construct($data);
    }

    /**
     * Get shopping cart resource model
     *
     * @return \Magento\Checkout\Model\Resource\Cart
     */
    protected function _getResource()
    {
        return $this->_resourceCart;
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * List of shopping cart items
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|array
     */
    public function getItems()
    {
        if (!$this->getQuote()->getId()) {
            return array();
        }
        return $this->getQuote()->getItemsCollection();
    }

    /**
     * Retrieve array of cart product ids
     *
     * @return array
     */
    public function getQuoteProductIds()
    {
        $products = $this->getData('product_ids');
        if (is_null($products)) {
            $products = array();
            foreach ($this->getQuote()->getAllItems() as $item) {
                $products[$item->getProductId()] = $item->getProductId();
            }
            $this->setData('product_ids', $products);
        }
        return $products;
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            $this->setData('quote', $this->_checkoutSession->getQuote());
        }
        return $this->_getData('quote');
    }

    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return \Magento\Checkout\Model\Cart
     */
    public function setQuote(\Magento\Sales\Model\Quote $quote)
    {
        $this->setData('quote', $quote);
        return $this;
    }

    /**
     * Initialize cart quote state to be able use it on cart page
     *
     * @return \Magento\Checkout\Model\Cart
     */
    public function init()
    {
        $quote = $this->getQuote()->setCheckoutMethod('');

        if ($this->_checkoutSession->getCheckoutState() !== \Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN) {
            $quote->removeAllAddresses()->removePayment();
            $this->_checkoutSession->resetCheckout();
        }

        if (!$quote->hasItems()) {
            $quote->getShippingAddress()->setCollectShippingRates(false)
                ->removeAllShippingRates();
        }

        return $this;
    }

    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param mixed $qtyFlag if is null set product qty like in order
     * @return \Magento\Checkout\Model\Cart
     */
    public function addOrderItem($orderItem, $qtyFlag=null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if (is_null($orderItem->getParentItem())) {
            $product = $this->_productFactory->create()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->load($orderItem->getProductId());
            if (!$product->getId()) {
                return $this;
            }

            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Object($info);
            if (is_null($qtyFlag)) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }

            $this->addProduct($product, $info);
        }
        return $this;
    }

    /**
     * Get product object based on requested product information
     *
     * @param   mixed $productInfo
     * @return  \Magento\Catalog\Model\Product
     * @throws \Magento\Core\Exception
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof \Magento\Catalog\Model\Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $product = $this->_productFactory->create()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->load($productInfo);
        }
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if (!$product
            || !$product->getId()
            || !is_array($product->getWebsiteIds())
            || !in_array($currentWebsiteId, $product->getWebsiteIds())
        ) {
            throw new \Magento\Core\Exception(__('We can\'t find the product.'));
        }
        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   mixed $requestInfo
     * @return  \Magento\Object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Object(array('qty' => $requestInfo));
        } else {
            $request = new \Magento\Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }

        return $request;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param mixed $requestInfo
     * @return \Magento\Checkout\Model\Cart
     * @throws \Magento\Core\Exception
     */
    public function addProduct($productInfo, $requestInfo=null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        $productId = $product->getId();

        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty
                && !$this->getQuote()->hasProductId($productId)
            ){
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Core\Exception $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                $redirectUrl = ($product->hasOptionsValidationFail())
                    ? $product->getUrlModel()->getUrl(
                        $product,
                        array('_query' => array('startcustomization' => 1))
                    )
                    : $product->getProductUrl();
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Core\Exception($result);
            }
        } else {
            throw new \Magento\Core\Exception(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch('checkout_cart_product_add_after', array(
            'quote_item' => $result,
            'product' => $product,
        ));
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Adding products to cart by ids
     *
     * @param   array $productIds
     * @return  \Magento\Checkout\Model\Cart
     */
    public function addProductsByIds($productIds)
    {
        $allAvailable = true;
        $allAdded     = true;

        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $productId = (int) $productId;
                if (!$productId) {
                    continue;
                }
                $product = $this->_getProduct($productId);
                if ($product->getId() && $product->isVisibleInCatalog()) {
                    try {
                        $this->getQuote()->addProduct($product);
                    } catch (\Exception $e){
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }

            if (!$allAvailable) {
                $this->_checkoutSession->addError(
                    __("We don't have some of the products you want.")
                );
            }
            if (!$allAdded) {
                $this->_checkoutSession->addError(
                    __("We don't have as many of some products as you want.")
                );
            }
        }
        return $this;
    }

    /**
     * Returns suggested quantities for items.
     * Can be used to automatically fix user entered quantities before updating cart
     * so that cart contains valid qty values
     *
     * $data is an array of ($quoteItemId => (item info array with 'qty' key), ...)
     *
     * @param   array $data
     * @return  array
     */
    public function suggestItemsQty($data)
    {
        foreach ($data as $itemId => $itemInfo) {
            if (!isset($itemInfo['qty'])) {
                continue;
            }
            $qty = (float) $itemInfo['qty'];
            if ($qty <= 0) {
                continue;
            }

            $quoteItem = $this->getQuote()->getItemById($itemId);
            if (!$quoteItem) {
                continue;
            }

            $product = $quoteItem->getProduct();
            if (!$product) {
                continue;
            }

            /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
            $stockItem = $product->getStockItem();
            if (!$stockItem) {
                continue;
            }

            $data[$itemId]['before_suggest_qty'] = $qty;
            $data[$itemId]['qty'] = $stockItem->suggestQty($qty);
        }

        return $data;
    }

    /**
     * Update cart items information
     *
     * @param   array $data
     * @return  \Magento\Checkout\Model\Cart
     * @throws \Magento\Core\Exception
     */
    public function updateItems($data)
    {
        $this->_eventManager->dispatch('checkout_cart_update_items_before', array('cart'=>$this, 'info'=>$data));

        $session = $this->_checkoutSession;
        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
            if ($qty > 0) {
                $item->setQty($qty);

                $itemInQuote = $this->getQuote()->getItemById($item->getId());

                if (!$itemInQuote && $item->getHasError()) {
                    throw new \Magento\Core\Exception($item->getMessage());
                }

                if (isset($itemInfo['before_suggest_qty']) && ($itemInfo['before_suggest_qty'] != $qty)) {
                    $qtyRecalculatedFlag = true;
                    $message = $this->_message->notice(__('Quantity was recalculated from %1 to %2', $itemInfo['before_suggest_qty'], $qty));
                    $session->addQuoteItemMessage($item->getId(), $message);
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $session->addNotice(
                __('Some products quantities were recalculated because of quantity increment mismatch.')
            );
        }

        $this->_eventManager->dispatch('checkout_cart_update_items_after', array('cart'=>$this, 'info'=>$data));
        return $this;
    }

    /**
     * Remove item from cart
     *
     * @param   int $itemId
     * @return  \Magento\Checkout\Model\Cart
     */
    public function removeItem($itemId)
    {
        $this->getQuote()->removeItem($itemId);
        return $this;
    }

    /**
     * Save cart
     *
     * @return \Magento\Checkout\Model\Cart
     */
    public function save()
    {
        $this->_eventManager->dispatch('checkout_cart_save_before', array('cart'=>$this));

        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();
        $this->getQuote()->save();
        $this->_checkoutSession->setQuoteId($this->getQuote()->getId());
        /**
         * Cart save usually called after changes with cart items.
         */
        $this->_eventManager->dispatch('checkout_cart_save_after', array('cart'=>$this));
        return $this;
    }

    /**
     * Save cart (implement interface method)
     */
    public function saveQuote()
    {
        $this->save();
    }

    /**
     * Mark all quote items as deleted (empty shopping cart)
     *
     * @return \Magento\Checkout\Model\Cart
     */
    public function truncate()
    {
        $this->getQuote()->removeAllItems();
        return $this;
    }

    /**
     * @return array|null
     */
    public function getProductIds()
    {
        if (null === $this->_productIds) {
            $this->_productIds = array();
            if ($this->getSummaryQty()>0) {
               foreach ($this->getQuote()->getAllItems() as $item) {
                   $this->_productIds[] = $item->getProductId();
               }
            }
            $this->_productIds = array_unique($this->_productIds);
        }
        return $this->_productIds;
    }

    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = $this->_checkoutSession->getQuoteId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && $this->_customerSession->isLoggedIn()) {
            $quote = $this->_checkoutSession->getQuote();
            $quoteId = $this->_checkoutSession->getQuoteId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            if ($this->_coreStoreConfig->getConfig('checkout/cart_link/use_qty')) {
                $this->_summaryQty = $this->getItemsQty();
            } else {
                $this->_summaryQty = $this->getItemsCount();
            }
        }
        return $this->_summaryQty;
    }

    /**
     * Get shopping cart items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getQuote()->getItemsCount()*1;
    }

    /**
     * Get shopping cart summary qty
     *
     * @return int|float
     */
    public function getItemsQty()
    {
        return $this->getQuote()->getItemsQty()*1;
    }

    /**
     * Update item in shopping cart (quote)
     * $requestInfo - either qty (int) or buyRequest in form of array or \Magento\Object
     * $updatingParams - information on how to perform update, passed to Quote->updateItem() method
     *
     * @see \Magento\Sales\Model\Quote::updateItem()
     *
     * @param int $itemId
     * @param int|array|\Magento\Object $requestInfo
     * @param null|array|\Magento\Object $updatingParams
     * @return \Magento\Sales\Model\Quote\Item|string
     * @throws \Magento\Core\Exception
     */
    public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
    {
        try {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new \Magento\Core\Exception(__('This quote item does not exist.'));
            }
            $productId = $item->getProduct()->getId();
            $product = $this->_getProduct($productId);
            $request = $this->_getProductRequest($requestInfo);

            if ($product->getStockItem()) {
                $minimumQty = $product->getStockItem()->getMinSaleQty();
                // If product was not found in cart and there is set minimal qty for it
                if ($minimumQty && ($minimumQty > 0)
                    && ($request->getQty() < $minimumQty)
                    && !$this->getQuote()->hasProductId($productId)
                ) {
                    $request->setQty($minimumQty);
                }
            }

            $result = $this->getQuote()->updateItem($itemId, $request, $updatingParams);
        } catch (\Magento\Core\Exception $e) {
            $this->_checkoutSession->setUseNotice(false);
            $result = $e->getMessage();
        }

        /**
         * We can get string if updating process had some errors
         */
        if (is_string($result)) {
            if ($this->_checkoutSession->getUseNotice() === null) {
                $this->_checkoutSession->setUseNotice(true);
            }
            throw new \Magento\Core\Exception($result);
        }

        $this->_eventManager->dispatch('checkout_cart_product_update_after', array(
            'quote_item' => $result,
            'product' => $product
        ));
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $result;
    }
}
