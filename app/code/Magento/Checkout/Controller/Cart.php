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
 * Shopping cart controller
 */
namespace Magento\Checkout\Controller;

class Cart
    extends \Magento\Core\Controller\Front\Action
    implements \Magento\Catalog\Controller\Product\View\ViewInterface
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Core\Model\Store\ConfigInterface $storeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Core\Model\Store\ConfigInterface $storeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->_storeConfig = $storeConfig;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return \Magento\Checkout\Model\Cart
     */
    protected function _getCart()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Cart');
    }

    /**
     * Get current active quote instance
     *
     * @return \Magento\Sales\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return \Magento\Checkout\Controller\Cart
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isUrlInternal($returnUrl)) {
            $this->_checkoutSession->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!$this->_storeConfig->getConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_checkoutSession->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getId();
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                ->setStoreId($storeId)
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $currencyCode = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()
                    ->getCurrentCurrencyCode();
                $minimumAmount = $this->_objectManager->get('Magento\Core\Model\LocaleInterface')
                    ->currency($currencyCode)
                    ->toCurrency($this->_storeConfig->getConfig('sales/minimum_order/amount'));

                $warning = $this->_storeConfig->getConfig('sales/minimum_order/description')
                    ? $this->_storeConfig->getConfig('sales/minimum_order/description')
                    : __('Minimum order amount is %1', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode($this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_checkoutSession->setCartWasUpdated(true);

        \Magento\Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('Magento\Checkout\Model\Session')
            ->_initLayoutMessages('Magento\Catalog\Model\Session')
            ->getLayout()->getBlock('head')->setTitle(__('Shopping Cart'));
        $this->renderLayout();
        \Magento\Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Add product to shopping cart action
     */
    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    array('locale' => $this->_objectManager->get('Magento\Core\Model\LocaleInterface')->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_checkoutSession->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            $this->_eventManager->dispatch('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = __('You added %1 to your shopping cart.', $this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($product->getName()));
                    $this->_checkoutSession->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (\Magento\Core\Exception $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->_checkoutSession->addNotice($this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_checkoutSession->addError($this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($message));
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer($this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl());
            }
        } catch (\Exception $e) {
            $this->_checkoutSession->addException($e, __('We cannot add this item to your shopping cart'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_goBack();
        }
    }

    public function addgroupAction()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());
        if (is_array($orderItemIds)) {
            $itemsCollection = $this->_objectManager->create('Magento\Sales\Model\Order\Item')
                ->getCollection()
                ->addIdFilter($orderItemIds)
                ->load();
            /* @var $itemsCollection \Magento\Sales\Model\Resource\Order\Item\Collection */
            $cart = $this->_getCart();
            foreach ($itemsCollection as $item) {
                try {
                    $cart->addOrderItem($item, 1);
                } catch (\Magento\Core\Exception $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->_checkoutSession->addNotice($e->getMessage());
                    } else {
                        $this->_checkoutSession->addError($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->_checkoutSession->addException($e, __('We cannot add this item to your shopping cart'));
                    $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
                    $this->_goBack();
                }
            }
            $cart->save();
            $this->_checkoutSession->setCartWasUpdated(true);
        }
        $this->_goBack();
    }

    /**
     * Action to reconfigure cart item
     */
    public function configureAction()
    {
        // Extract item and product to configure
        $id = (int) $this->getRequest()->getParam('id');
        $quoteItem = null;
        $cart = $this->_getCart();
        if ($id) {
            $quoteItem = $cart->getQuote()->getItemById($id);
        }

        if (!$quoteItem) {
            $this->_checkoutSession->addError(__("We can't find the quote item."));
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $params = new \Magento\Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            $this->_objectManager->get('Magento\Catalog\Helper\Product\View')->prepareAndRender(
                $quoteItem->getProduct()->getId(), $this, $params
            );
        } catch (\Exception $e) {
            $this->_checkoutSession->addError(__('We cannot configure the product.'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_goBack();
            return;
        }
    }

    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    array('locale' => $this->_objectManager->get('Magento\Core\Model\LocaleInterface')->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                throw new \Magento\Core\Exception(__("We can't find the quote item."));
            }

            $item = $cart->updateItem($id, new \Magento\Object($params));
            if (is_string($item)) {
                throw new \Magento\Core\Exception($item);
            }
            if ($item->getHasError()) {
                throw new \Magento\Core\Exception($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_checkoutSession->setCartWasUpdated(true);

            $this->_eventManager->dispatch('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = __('%1 was updated in your shopping cart.', $this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($item->getProduct()->getName()));
                    $this->_checkoutSession->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (\Magento\Core\Exception $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->_checkoutSession->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_checkoutSession->addError($message);
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer($this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl());
            }
        } catch (\Exception $e) {
            $this->_checkoutSession->addException($e, __('We cannot update the item.'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }

    /**
     * Update shopping cart data action
     */
    public function updatePostAction()
    {
        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        $this->_goBack();
    }

    /**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    array('locale' => $this->_objectManager->get('Magento\Core\Model\LocaleInterface')->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_checkoutSession->setCartWasUpdated(true);
        } catch (\Magento\Core\Exception $e) {
            $this->_checkoutSession->addError($this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($e->getMessage()));
        } catch (\Exception $e) {
            $this->_checkoutSession->addException($e, __('We cannot update the shopping cart.'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        }
    }

    /**
     * Empty customer's shopping cart
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_checkoutSession->setCartWasUpdated(true);
        } catch (\Magento\Core\Exception $exception) {
            $this->_checkoutSession->addError($exception->getMessage());
        } catch (\Exception $exception) {
            $this->_checkoutSession->addException($exception, __('We cannot update the shopping cart.'));
        }
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
            } catch (\Exception $e) {
                $this->_checkoutSession->addError(__('We cannot remove the item.'));
                $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            }
        }
        $this->_redirectReferer($this->_objectManager->create('Magento\UrlInterface')->getUrl('*/*'));
    }

    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();
        $this->_goBack();
    }

    public function estimateUpdatePostAction()
    {
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_checkoutSession->addSuccess(
                        __('The coupon code "%1" was applied.', $this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($couponCode))
                    );
                } else {
                    $this->_checkoutSession->addError(
                        __('The coupon code "%1" is not valid.', $this->_objectManager->get('Magento\Core\Helper\Data')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_checkoutSession->addSuccess(__('The coupon code was canceled.'));
            }

        } catch (\Magento\Core\Exception $e) {
            $this->_checkoutSession->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_checkoutSession->addError(__('We cannot apply the coupon code.'));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        }

        $this->_goBack();
    }
}
