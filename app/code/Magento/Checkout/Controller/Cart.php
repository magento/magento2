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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Controller;

use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Shopping cart controller
 */
class Cart extends \Magento\App\Action\Action implements \Magento\Catalog\Controller\Product\View\ViewInterface
{
    /**
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Core\Model\Store\ConfigInterface $storeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param CustomerCart $cart
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Core\Model\Store\ConfigInterface $storeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        CustomerCart $cart
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_storeConfig = $storeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * Set back redirect url to response
     *
     * @return $this
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!$this->_storeConfig->getConfig(
            'checkout/cart/redirect_to_cart'
        ) && !$this->getRequest()->getParam(
            'in_cart'
        ) && ($backUrl = $this->_redirect->getRefererUrl())
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if ($this->getRequest()->getActionName() == 'add' && !$this->getRequest()->getParam('in_cart')) {
                $this->_checkoutSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
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
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getId();
            $product = $this->_objectManager->create(
                'Magento\Catalog\Model\Product'
            )->setStoreId(
                $storeId
            )->load(
                $productId
            );
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Shopping cart display action
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->cart->getQuote()->getItemsCount()) {
            $this->cart->init();
            $this->cart->save();

            if (!$this->cart->getQuote()->validateMinimumAmount()) {
                $currencyCode = $this->_objectManager->get(
                    'Magento\Core\Model\StoreManagerInterface'
                )->getStore()->getCurrentCurrencyCode();
                $minimumAmount = $this->_objectManager->get(
                    'Magento\Locale\CurrencyInterface'
                )->getCurrency(
                    $currencyCode
                )->toCurrency(
                    $this->_storeConfig->getConfig('sales/minimum_order/amount')
                );

                $warning = $this->_storeConfig->getConfig(
                    'sales/minimum_order/description'
                ) ? $this->_storeConfig->getConfig(
                    'sales/minimum_order/description'
                ) : __(
                    'Minimum order amount is %1',
                    $minimumAmount
                );

                $this->messageManager->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        /** @var \Magento\Message\MessageInterface $message  */
        foreach ($this->cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setText($this->_objectManager->get('Magento\Escaper')->escapeHtml($message->getText()));
                $messages[] = $message;
            }
        }
        $this->messageManager->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_checkoutSession->setCartWasUpdated(true);

        \Magento\Profiler::start(__METHOD__ . 'cart_display');

        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $layout->initMessages();
        $layout->getBlock('head')->setTitle(__('Shopping Cart'));
        $this->_view->renderLayout();
        \Magento\Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Add product to shopping cart action
     *
     * @return void
     */
    public function addAction()
    {
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    array('locale' => $this->_objectManager->get('Magento\Locale\ResolverInterface')->getLocaleCode())
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

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_checkoutSession->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $this->_objectManager->get('Magento\Escaper')->escapeHtml($product->getName())
                    );
                    $this->messageManager->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (\Magento\Model\Exception $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get('Magento\Escaper')->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get('Magento\Escaper')->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($cartUrl));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot add this item to your shopping cart'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->_goBack();
        }
    }

    /**
     * @return void
     */
    public function addgroupAction()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());
        if (is_array($orderItemIds)) {
            $itemsCollection = $this->_objectManager->create(
                'Magento\Sales\Model\Order\Item'
            )->getCollection()->addIdFilter(
                $orderItemIds
            )->load();
            /* @var $itemsCollection \Magento\Sales\Model\Resource\Order\Item\Collection */
            foreach ($itemsCollection as $item) {
                try {
                    $this->cart->addOrderItem($item, 1);
                } catch (\Magento\Model\Exception $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice($e->getMessage());
                    } else {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('We cannot add this item to your shopping cart'));
                    $this->_objectManager->get('Magento\Logger')->logException($e);
                    $this->_goBack();
                }
            }
            $this->cart->save();
            $this->_checkoutSession->setCartWasUpdated(true);
        }
        $this->_goBack();
    }

    /**
     * Action to reconfigure cart item
     *
     * @return void
     */
    public function configureAction()
    {
        // Extract item and product to configure
        $id = (int)$this->getRequest()->getParam('id');
        $quoteItem = null;
        if ($id) {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
        }

        if (!$quoteItem) {
            $this->messageManager->addError(__("We can't find the quote item."));
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $params = new \Magento\Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            $this->_objectManager->get(
                'Magento\Catalog\Helper\Product\View'
            )->prepareAndRender(
                $quoteItem->getProduct()->getId(),
                $this,
                $params
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot configure the product.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->_goBack();
            return;
        }
    }

    /**
     * Update product configuration for a cart item
     *
     * @return void
     */
    public function updateItemOptionsAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    array('locale' => $this->_objectManager->get('Magento\Locale\ResolverInterface')->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $this->cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                throw new \Magento\Model\Exception(__("We can't find the quote item."));
            }

            $item = $this->cart->updateItem($id, new \Magento\Object($params));
            if (is_string($item)) {
                throw new \Magento\Model\Exception($item);
            }
            if ($item->getHasError()) {
                throw new \Magento\Model\Exception($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_checkoutSession->setCartWasUpdated(true);

            $this->_eventManager->dispatch(
                'checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        '%1 was updated in your shopping cart.',
                        $this->_objectManager->get('Magento\Escaper')->escapeHtml($item->getProduct()->getName())
                    );
                    $this->messageManager->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (\Magento\Model\Exception $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError($message);
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($cartUrl));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot update the item.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }

    /**
     * Update shopping cart data action
     *
     * @return void
     */
    public function updatePostAction()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/');
            return;
        }

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
     *
     * @return void
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    array('locale' => $this->_objectManager->get('Magento\Locale\ResolverInterface')->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }

                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData)->save();
            }
            $this->_checkoutSession->setCartWasUpdated(true);
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError(
                $this->_objectManager->get('Magento\Escaper')->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot update the shopping cart.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
    }

    /**
     * Empty customer's shopping cart
     *
     * @return void
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->cart->truncate()->save();
            $this->_checkoutSession->setCartWasUpdated(true);
        } catch (\Magento\Model\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addException($exception, __('We cannot update the shopping cart.'));
        }
    }

    /**
     * Delete shopping cart item action
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->cart->removeItem($id)->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We cannot remove the item.'));
                $this->_objectManager->get('Magento\Logger')->logException($e);
            }
        }
        $defaultUrl = $this->_objectManager->create('Magento\UrlInterface')->getUrl('*/*');
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($defaultUrl));
    }

    /**
     * Initialize shipping information
     *
     * @return void
     */
    public function estimatePostAction()
    {
        $country = (string)$this->getRequest()->getParam('country_id');
        $postcode = (string)$this->getRequest()->getParam('estimate_postcode');
        $city = (string)$this->getRequest()->getParam('estimate_city');
        $regionId = (string)$this->getRequest()->getParam('region_id');
        $region = (string)$this->getRequest()->getParam('region');

        $this->cart->getQuote()->getShippingAddress()->setCountryId(
            $country
        )->setCity(
            $city
        )->setPostcode(
            $postcode
        )->setRegionId(
            $regionId
        )->setRegion(
            $region
        )->setCollectShippingRates(
            true
        );
        $this->cart->getQuote()->save();
        $this->_goBack();
    }

    /**
     * @return void
     */
    public function estimateUpdatePostAction()
    {
        $code = (string)$this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->cart->getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    /**
     * Initialize coupon
     *
     * @return void
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->cart->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = $this->getRequest()->getParam(
            'remove'
        ) == 1 ? '' : trim(
            $this->getRequest()->getParam('coupon_code')
        );
        $oldCouponCode = $this->cart->getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->cart->getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals()->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->cart->getQuote()->getCouponCode()) {
                    $this->messageManager->addSuccess(
                        __(
                            'The coupon code "%1" was applied.',
                            $this->_objectManager->get('Magento\Escaper')->escapeHtml($couponCode)
                        )
                    );
                } else {
                    $this->messageManager->addError(
                        __(
                            'The coupon code "%1" is not valid.',
                            $this->_objectManager->get('Magento\Escaper')->escapeHtml($couponCode)
                        )
                    );
                }
            } else {
                $this->messageManager->addSuccess(__('The coupon code was canceled.'));
            }
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the coupon code.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }

        $this->_goBack();
    }

    /**
     * Check if URL corresponds store
     *
     * @param string $url
     * @return bool
     */
    protected function _isInternalUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }

        /**
         * Url must start from base secure or base unsecure url
         */
        /** @var $store \Magento\Core\Model\Store */
        $store = $this->_storeManager->getStore();
        $unsecure = strpos($url, $store->getBaseUrl()) === 0;
        $secure = strpos($url, $store->getBaseUrl(\Magento\UrlInterface::URL_TYPE_LINK, true)) === 0;
        return $unsecure || $secure;
    }
}
