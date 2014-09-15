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
namespace Magento\Checkout\Helper;

/**
 * Shopping cart helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cart extends \Magento\Core\Helper\Url
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
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

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
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_coreData = $coreData;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutCart = $checkoutCart;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $storeManager);
    }

    /**
     * Retrieve cart instance
     *
     * @return \Magento\Checkout\Model\Cart
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
    public function getAddUrl($product, $additional = array())
    {
        $continueUrl = $this->_coreData->urlEncode($this->_urlBuilder->getCurrentUrl());
        $urlParamName = \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED;

        $routeParams = array($urlParamName => $continueUrl, 'product' => $product->getEntityId());

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_scope_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'checkout' && $this->_getRequest()->getControllerName() == 'cart'
        ) {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('checkout/cart/add', $routeParams);
    }

    /**
     * Retrieve url for remove product from cart
     *
     * @param   \Magento\Sales\Model\Quote\Item $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        $params = array(
            'id' => $item->getId(),
            \Magento\Framework\App\Action\Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
        );
        return $this->_getUrl(self::DELETE_URL, $params);
    }

    /**
     * Get post parameters for delete from cart
     *
     * @param \Magento\Sales\Model\Quote\Item $item
     * @return string
     */
    public function getDeletePostJson($item)
    {
        $url = $this->_getUrl(self::DELETE_URL);

        $data = ['id' => $item->getId()];
        if (!$this->_request->isAjax()) {
            $data[\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED] = $this->getCurrentBase64Url();
        }
        return json_encode(array('action' => $url, 'data' => $data));
    }

    /**
     * Retrieve shopping cart url
     *
     * @return string
     */
    public function getCartUrl()
    {
        return $this->_getUrl('checkout/cart');
    }

    /**
     * Retrieve current quote instance
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get shopping cart items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getCart()->getItemsCount();
    }

    /**
     * Get shopping cart summary qty
     *
     * @return int|float
     */
    public function getItemsQty()
    {
        return $this->getCart()->getItemsQty();
    }

    /**
     * Get shopping cart items summary (include config settings)
     *
     * @return int|float
     */
    public function getSummaryCount()
    {
        return $this->getCart()->getSummaryQty();
    }

    /**
     * Check quote for virtual products only
     *
     * @return bool
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
     */
    public function getShouldRedirectToCart($store = null)
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_REDIRECT_TO_CART, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}
