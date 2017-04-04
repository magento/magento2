<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

use Magento\Paypal\Controller\Payflow;
use Magento\Paypal\Model\Config;
use Magento\Sales\Model\Order;

class ReturnUrl extends Payflow
{
    /**
     * @var array of allowed order states on frontend
     */
    protected $allowedOrderStates = [
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE,
    ];

    /**
     * Payment method code
     * @var string
     */
    protected $allowedPaymentMethodCodes = [
        Config::METHOD_PAYFLOWPRO,
        Config::METHOD_PAYFLOWLINK
    ];

    /**
     * When a customer return to website from payflow gateway.
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        /** @var \Magento\Checkout\Block\Onepage\Success $redirectBlock */
        $redirectBlock = $this->_view->getLayout()->getBlock($this->_redirectBlockName);

        if ($this->_checkoutSession->getLastRealOrderId()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_orderFactory->create()->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());

            if ($order->getIncrementId()) {
                if ($this->checkOrderState($order)) {
                    $redirectBlock->setData('goto_success_page', true);
                } else {
                    if ($this->checkPaymentMethod($order)) {
                        $gotoSection = $this->_cancelPayment(strval($this->getRequest()->getParam('RESPMSG')));
                        $redirectBlock->setData('goto_section', $gotoSection);
                        $redirectBlock->setData('error_msg', __('Your payment has been declined. Please try again.'));
                    } else {
                        $redirectBlock->setData('goto_section', false);
                        $redirectBlock->setData('error_msg', __('Requested payment method does not match with order.'));
                    }
                }
            }
        }

        $this->_view->renderLayout();
    }

    /**
     * Check order state
     *
     * @param Order $order
     * @return bool
     */
    protected function checkOrderState(Order $order)
    {
        return in_array($order->getState(), $this->allowedOrderStates);
    }

    /**
     * Check requested payment method
     *
     * @param Order $order
     * @return bool
     */
    protected function checkPaymentMethod(Order $order)
    {
        $payment = $order->getPayment();
        return in_array($payment->getMethod(), $this->allowedPaymentMethodCodes);
    }
}
