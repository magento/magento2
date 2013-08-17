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
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart controller
 */
class Mage_Checkout_CartController
    extends Mage_Core_Controller_Front_Action
    implements Mage_Catalog_Controller_Product_View_Interface
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * @var Mage_Core_Model_Store_ConfigInterface
     */
    protected $_storeConfig;

    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $_checkoutSession;

    /**
     * @param Mage_Core_Controller_Varien_Action_Context $context
     * @param Mage_Core_Model_Store_ConfigInterface $storeConfig
     * @param Mage_Checkout_Model_Session $checkoutSession
     * @param string $areaCode
     */
    public function __construct(
        Mage_Core_Controller_Varien_Action_Context $context,
        Mage_Core_Model_Store_ConfigInterface $storeConfig,
        Mage_Checkout_Model_Session $checkoutSession,
        $areaCode = null
    ) {
        parent::__construct($context, $areaCode);
        $this->_storeConfig = $storeConfig;
        $this->_checkoutSession = $checkoutSession;
    }


    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Cart');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
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
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId(Mage::app()->getStore()->getId())
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
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency($this->_storeConfig->getConfig('sales/minimum_order/amount'));

                $warning = $this->_storeConfig->getConfig('sales/minimum_order/description')
                    ? $this->_storeConfig->getConfig('sales/minimum_order/description')
                    : Mage::helper('Mage_Checkout_Helper_Data')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('Mage_Core_Helper_Data')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_checkoutSession->setCartWasUpdated(true);

        Magento_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('Mage_Checkout_Model_Session')
            ->_initLayoutMessages('Mage_Catalog_Model_Session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Magento_Profiler::stop(__METHOD__ . 'cart_display');
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
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
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
                    $message = $this->__('You added %s to your shopping cart.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($product->getName()));
                    $this->_checkoutSession->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->_checkoutSession->addNotice(Mage::helper('Mage_Core_Helper_Data')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_checkoutSession->addError(Mage::helper('Mage_Core_Helper_Data')->escapeHtml($message));
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('Mage_Checkout_Helper_Cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_checkoutSession->addException($e, $this->__('We cannot add this item to your shopping cart'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

    public function addgroupAction()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());
        if (is_array($orderItemIds)) {
            $itemsCollection = Mage::getModel('Mage_Sales_Model_Order_Item')
                ->getCollection()
                ->addIdFilter($orderItemIds)
                ->load();
            /* @var $itemsCollection Mage_Sales_Model_Resource_Order_Item_Collection */
            $cart = $this->_getCart();
            foreach ($itemsCollection as $item) {
                try {
                    $cart->addOrderItem($item, 1);
                } catch (Mage_Core_Exception $e) {
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->_checkoutSession->addNotice($e->getMessage());
                    } else {
                        $this->_checkoutSession->addError($e->getMessage());
                    }
                } catch (Exception $e) {
                    $this->_checkoutSession->addException($e, $this->__('We cannot add this item to your shopping cart'));
                    Mage::logException($e);
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
            $this->_checkoutSession->addError($this->__("We can't find the quote item."));
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            Mage::helper('Mage_Catalog_Helper_Product_View')->prepareAndRender(
                $quoteItem->getProduct()->getId(), $this, $params
            );
        } catch (Exception $e) {
            $this->_checkoutSession->addError($this->__('We cannot configure the product.'));
            Mage::logException($e);
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
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__("We can't find the quote item."));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
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
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($item->getProduct()->getName()));
                    $this->_checkoutSession->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
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
                $this->_redirectReferer(Mage::helper('Mage_Checkout_Helper_Cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_checkoutSession->addException($e, $this->__('We cannot update the item.'));
            Mage::logException($e);
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
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
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
        } catch (Mage_Core_Exception $e) {
            $this->_checkoutSession->addError(Mage::helper('Mage_Core_Helper_Data')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_checkoutSession->addException($e, $this->__('We cannot update the shopping cart.'));
            Mage::logException($e);
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
        } catch (Mage_Core_Exception $exception) {
            $this->_checkoutSession->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_checkoutSession->addException($exception, $this->__('We cannot update the shopping cart.'));
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
            } catch (Exception $e) {
                $this->_checkoutSession->addError($this->__('We cannot remove the item.'));
                Mage::logException($e);
            }
        }
        $this->_redirectReferer(Mage::getUrl('*/*'));
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
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_checkoutSession->addSuccess(
                        $this->__('The coupon code "%s" was applied.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($couponCode))
                    );
                } else {
                    $this->_checkoutSession->addError(
                        $this->__('The coupon code "%s" is not valid.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_checkoutSession->addSuccess($this->__('The coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_checkoutSession->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_checkoutSession->addError($this->__('We cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }
}
