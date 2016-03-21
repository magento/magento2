<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Helper;

/**
 * Shopping cart helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cart extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Path to controller to delete item from cart
     */
    const DELETE_URL = 'checkout/cart/delete';

    /**
     * Path for redirect to cart
     */
    const XML_PATH_REDIRECT_TO_CART = 'checkout/cart/redirect_to_cart';

    /**
     * Maximal coupon code length according to database table definitions (longer codes are truncated)
     */
    const COUPON_CODE_MAX_LENGTH = 255;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutCart = $checkoutCart;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Retrieve cart instance
     *
     * @return \Magento\Checkout\Model\Cart
     * @codeCoverageIgnore
     */
    public function getCart()
    {
        return $this->_checkoutCart;
    }

    /**
     * Retrieve url for add product to cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return  string
     */
    public function getAddUrl($product, $additional = [])
    {
        $continueUrl = $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
        $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;

        $routeParams = [
            $urlParamName => $continueUrl,
            'product' => $product->getEntityId(),
            '_secure' => $this->_getRequest()->isSecure()
        ];

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_scope_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('checkout/cart/add', $routeParams);
    }

    /**
     * Retrieve url for remove product from cart
     *
     * @param   \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        $params = [
            'id' => $item->getId(),
            \Magento\Framework\App\ActionInterface::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url(),
        ];
        return $this->_getUrl(self::DELETE_URL, $params);
    }

    /**
     * Get post parameters for delete from cart
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return string
     */
    public function getDeletePostJson($item)
    {
        $url = $this->_getUrl(self::DELETE_URL);

        $data = ['id' => $item->getId()];
        if (!$this->_request->isAjax()) {
            $data[\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED] = $this->getCurrentBase64Url();
        }
        return json_encode(['action' => $url, 'data' => $data]);
    }

    /**
     * Retrieve shopping cart url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCartUrl()
    {
        return $this->_getUrl('checkout/cart');
    }

    /**
     * Retrieve current quote instance
     *
     * @return \Magento\Quote\Model\Quote
     * @codeCoverageIgnore
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get shopping cart items count
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getItemsCount()
    {
        return $this->getCart()->getItemsCount();
    }

    /**
     * Get shopping cart summary qty
     *
     * @return int|float
     * @codeCoverageIgnore
     */
    public function getItemsQty()
    {
        return $this->getCart()->getItemsQty();
    }

    /**
     * Get shopping cart items summary (include config settings)
     *
     * @return int|float
     * @codeCoverageIgnore
     */
    public function getSummaryCount()
    {
        return $this->getCart()->getSummaryQty();
    }

    /**
     * Check quote for virtual products only
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @codeCoverageIgnore
     */
    public function getIsVirtualQuote()
    {
        return $this->getQuote()->isVirtual();
    }

    /**
     * Checks if customer should be redirected to shopping cart after adding a product
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @codeCoverageIgnore
     */
    public function getShouldRedirectToCart($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REDIRECT_TO_CART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
