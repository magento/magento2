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
    private const ORDER_INCREMENT_ID = 'INVNUM';

    private const SILENT_POST_HASH = 'secure_silent_post_hash';

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
        $order = $this->getOrderFromRequest();
        if ($order) {
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

        $this->_view->renderLayout();
    }

    /**
     * Returns an order from request.
     *
     * @return Order|null
     */
    private function getOrderFromRequest(): ?Order
    {
        $orderId = $this->getRequest()->getParam(self::ORDER_INCREMENT_ID);
        if (!$orderId) {
            return null;
        }

        $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
        $storedHash = (string)$order->getPayment()->getAdditionalInformation(self::SILENT_POST_HASH);
        $requestHash = (string)$this->getRequest()->getParam('USER2');
        if (empty($storedHash) || empty($requestHash) || !hash_equals($storedHash, $requestHash)) {
            return null;
        }
        $this->_checkoutSession->setLastRealOrderId($orderId);

        return $order;
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
