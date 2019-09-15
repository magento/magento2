<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Paypal\Controller\Payflow;
use Magento\Paypal\Model\Config;
use Magento\Sales\Model\Order;

/**
 * Paypal Payflow ReturnUrl controller class
 */
class ReturnUrl extends Payflow implements CsrfAwareActionInterface, HttpGetActionInterface
{
    /**
     * @var array of allowed order states on frontend
     */
    protected $allowedOrderStates = [
        Order::STATE_PROCESSING,
        Order::STATE_COMPLETE,
        Order::STATE_PAYMENT_REVIEW
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
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

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
                        $gotoSection = $this->_cancelPayment((string)$this->getRequest()->getParam('RESPMSG'));
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
