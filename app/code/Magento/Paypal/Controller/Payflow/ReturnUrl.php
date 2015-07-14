<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

use Magento\Paypal\Controller\Payflow;
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
                if (in_array($order->getState(), $this->allowedOrderStates)) {
                    $redirectBlock->setData('goto_success_page', true);
                } else {
                    $gotoSection = $this->_cancelPayment(strval($this->getRequest()->getParam('RESPMSG')));
                    $redirectBlock->setData('goto_section', $gotoSection);
                    $redirectBlock->setData('error_msg', __('Your payment has been declined. Please try again.'));
                }
            }
        }

        $this->_view->renderLayout();
    }
}
