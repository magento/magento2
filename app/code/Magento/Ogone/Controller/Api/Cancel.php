<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ogone\Controller\Api;

class Cancel extends \Magento\Ogone\Controller\Api
{
    /**
     * Process cancel action by cancel url
     *
     * @return $this
     */
    public function _cancelProcess()
    {
        $status = \Magento\Ogone\Model\Api::CANCEL_OGONE_STATUS;
        $comment = __('The order was canceled on the Ogone side.');
        $this->_cancelOrder($status, $comment);
        return $this;
    }

    /**
     * When user cancel the payment
     * change order status to cancelled
     * need to redirect user to shopping cart
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_validateOgoneData()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_getCheckout()->setQuoteId($this->_getCheckout()->getOgoneQuoteId());
        $this->_cancelProcess();
    }
}
