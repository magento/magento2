<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

class ReturnAction extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Return from PayPal and dispatch customer to order review page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('retry_authorization') == 'true'
            && is_array($this->_getCheckoutSession()->getPaypalTransactionData())
        ) {
            $this->_forward('placeOrder');
            return;
        }
        try {
            $this->_getCheckoutSession()->unsPaypalTransactionData();
            $this->_initCheckout();
            $this->_checkout->returnFromPaypal($this->_initToken());
            if ($this->_checkout->canSkipOrderReviewStep()) {
                $this->_forward('placeOrder');
            } else {
                $this->_redirect('*/*/review');
            }
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t process Express Checkout approval.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->_redirect('checkout/cart');
    }
}
