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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect shopping cart controller
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_CartController extends Mage_XmlConnect_Controller_Action
{
    /**
     * Shopping cart display action
     *
     * @return null
     */
    public function indexAction()
    {
        try {
            $messages = array();
            $cart = $this->_getCart();
            if ($cart->getQuote()->getItemsCount()) {
                $cart->init();
                $cart->save();

                if (!$this->_getQuote()->validateMinimumAmount()) {
                    $warning = Mage::getStoreConfig('sales/minimum_order/description');
                    $messages[parent::MESSAGE_STATUS_WARNING][] = $warning;
                }
            }

            foreach ($cart->getQuote()->getMessages() as $message) {
                if ($message) {
                    $messages[$message->getType()][] = $message->getText();
                }
            }

            /**
             * if customer enters shopping cart we should mark quote
             * as modified bc he can has checkout page in another window.
             */
            $this->_getSession()->setCartWasUpdated(true);
            $this->loadLayout(false)->getLayout()->getBlock('xmlconnect.cart')->setMessages($messages);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_message($this->__('Can\'t load cart.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Update shoping cart data action
     *
     * @return null
     */
    public function updateAction()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter($data['qty']);
                    }
                }
                $cart = $this->_getCart();
                if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }
                $cart->updateItems($cartData)->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
            $this->_message($this->__('Cart has been updated.'), parent::MESSAGE_STATUS_SUCCESS);
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_message($this->__('Can\'t update cart.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param mixed $requestInfo
     * @return Varien_Object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object();
            $request->setQty($requestInfo);
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        return $request;
    }

    /**
     * Add product to shopping cart action
     *
     * @return null
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

            $product = null;
            $productId = (int) $this->getRequest()->getParam('product');
            if ($productId) {
                $_product = Mage::getModel('Mage_Catalog_Model_Product')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                if ($_product->getId()) {
                    $product = $_product;
                }
            }
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_message($this->__('Product is unavailable.'), parent::MESSAGE_STATUS_ERROR);
                return;
            }

            if ($product->isConfigurable()) {

                $request = $this->_getProductRequest($params);
                /**
                 * Hardcoded Configurable product default
                 * Set min required qty for a product if it's need
                 */
                $qty = isset($params['qty']) ? $params['qty'] : 0;
                $requestedQty = ($qty > 1) ? $qty : 1;
                $subProduct = $product->getTypeInstance()
                    ->getProductByAttributes($request->getSuperAttribute(), $product);

                if (!empty($subProduct)
                    && $requestedQty < ($requiredQty = $subProduct->getStockItem()->getMinSaleQty())
                ) {
                    $requestedQty = $requiredQty;
                }

                $params['qty'] = $requestedQty;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);

            if (isset($params['whishlist_id'])) {
                $wishlist = $this->_getWishlist();
                $id = (int) $params['whishlist_id'];
                $item = Mage::getModel('Mage_Wishlist_Model_Item')->load($id);

                if ($item->getWishlistId() == $wishlist->getId()) {
                    try {
                        $item->delete();
                        $wishlist->save();
                    } catch (Mage_Core_Exception $e) {
                        $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
                    } catch(Exception $e) {
                        $this->_message(
                            $this->__('An error occurred while removing item from wishlist.'),
                            self::MESSAGE_STATUS_ERROR
                        );
                    }
                } else {
                    $wishlistMessage = $this->__('Specified item does not exist in wishlist.');
                }
                Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
            }

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (isset($wishlistMessage)) {
                    $this->_message($wishlistMessage, self::MESSAGE_STATUS_ERROR);
                } else {
                    $productName = Mage::helper('Mage_Core_Helper_Data')->escapeHtml($product->getName());
                    $message = $this->__('%s has been added to your cart.', $productName);
                    if ($cart->getQuote()->getHasError()) {
                        $message .= $this->__(' But cart has some errors.');
                    }
                    $this->_message($message, parent::MESSAGE_STATUS_SUCCESS);
                }
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_message($e->getMessage(), parent::MESSAGE_STATUS_ERROR);
            } else {
                $messageText = implode("\n", array_unique(explode("\n", $e->getMessage())));
                $this->_message($messageText, parent::MESSAGE_STATUS_ERROR);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_message($this->__('Can\'t add item to shopping cart.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Delete shoping cart item action
     *
     * @return null
     */
    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('item_id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();
                $this->_message($this->__('Item has been deleted from cart.'), parent::MESSAGE_STATUS_SUCCESS);
            } catch (Mage_Core_Exception $e) {
                $this->_message($e->getMessage(), parent::MESSAGE_STATUS_ERROR);
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_message($this->__('Can\'t remove the item.'), self::MESSAGE_STATUS_ERROR);
            }
        }
    }

    /**
     * Initialize coupon
     *
     * @return null
     */
    public function couponAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getQuote()->getItemsCount()) {
            $this->_message($this->__('Shopping cart is empty.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_message($this->__('Coupon code is empty.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')->collectTotals()->save();

            if ($couponCode) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_message(
                        $this->__('Coupon code %s was applied.', strip_tags($couponCode)),
                        parent::MESSAGE_STATUS_SUCCESS
                    );
                } else {
                    $this->_message(
                        $this->__('Coupon code %s is not valid.', strip_tags($couponCode)),
                        self::MESSAGE_STATUS_ERROR
                    );
                }
            } else {
                $this->_message($this->__('Coupon code was canceled.'), parent::MESSAGE_STATUS_SUCCESS);
            }

        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_message($this->__('Can\'t apply the coupon code.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Add Gift Card action
     *
     * @return null
     */
    public function addGiftcardAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getQuote()->getItemsCount()) {
            $this->_message($this->__('Shopping cart is empty.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $data = $this->getRequest()->getPost();
        if (!empty($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                Mage::getModel('Enterprise_GiftCardAccount_Model_Giftcardaccount')->loadByCode($code)->addToCart();
                $this->_message(
                    $this->__('Gift Card "%s" was added.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($code)),
                    self::MESSAGE_STATUS_SUCCESS
                );
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $code));
                $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
            } catch (Exception $e) {
                $this->_message($this->__('Cannot apply gift card.'), self::MESSAGE_STATUS_ERROR);
                Mage::logException($e);
            }
        } else {
            $this->_message($this->__('Gift Card code is empty.'), self::MESSAGE_STATUS_ERROR);
            return;
        }
    }

    /**
     * Remove Gift Card action
     *
     * @return null
     */
    public function removeGiftcardAction()
    {
        $code = $this->getRequest()->getParam('giftcard_code');
        if ($code) {
            try {
                Mage::getModel('Enterprise_GiftCardAccount_Model_Giftcardaccount')->loadByCode($code)->removeFromCart();
                $this->_message(
                    $this->__('Gift Card "%s" was removed.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($code)),
                    self::MESSAGE_STATUS_SUCCESS
                );
            } catch (Mage_Core_Exception $e) {
                $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
            } catch (Exception $e) {
                $this->_message($this->__('Cannot remove gift card.'), self::MESSAGE_STATUS_ERROR);
                Mage::logException($e);
            }
        } else {
            $this->_message($this->__('Gift Card code is empty.'), self::MESSAGE_STATUS_ERROR);
            return;
        }
    }

    /**
     * Remove Store Credit action
     *
     * @return null
     */
    public function removeStoreCreditAction()
    {
        if (!Mage::helper('Enterprise_CustomerBalance_Helper_Data')->isEnabled()) {
            $this->_message($this->__('Customer balance is disabled for current store'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $quote = $this->_getQuote();

        if ($quote->getUseCustomerBalance()) {
            $this->_message(
                $this->__('The store credit payment has been removed from shopping cart.'),
                self::MESSAGE_STATUS_SUCCESS
            );
            $quote->setUseCustomerBalance(false)->collectTotals()->save();
            return;
        } else {
            $this->_message(
                $this->__('Store Credit payment is not being used in your shopping cart.'),
                self::MESSAGE_STATUS_ERROR
            );
            return;
        }
    }

    /**
     * Get shopping cart summary and flag is_virtual
     *
     * @return null
     */
    public function infoAction()
    {
        try {
            $this->_getQuote()->collectTotals()->save();
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_message($this->__('Can\'t load cart info.'), self::MESSAGE_STATUS_ERROR);
        }
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
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Session');
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
     * Retrieve wishlist object
     *
     * @return Mage_Wishlist_Model_Wishlist|false
     */
    protected function _getWishlist()
    {
        try {
            $wishlist = Mage::getModel('Mage_Wishlist_Model_Wishlist')
                ->loadByCustomer(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
            return false;
        } catch (Exception $e) {
            $this->_message($this->__('Can\'t create wishlist.'), self::MESSAGE_STATUS_ERROR);
            return false;
        }
        return $wishlist;
    }
}
