<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

class UpdatePost extends \Magento\Checkout\Controller\Cart
{
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
        } catch (\Magento\Framework\Model\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addException($exception, __('We cannot update the shopping cart.'));
        }
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
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocaleCode()]
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
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError(
                $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot update the shopping cart.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
    }

    /**
     * Update shopping cart data action
     *
     * @return void
     */
    public function execute()
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
}
