<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class VoidPayment extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Attempt to void the order payment
     *
     * @return void
     */
    public function execute()
    {
        if (!($order = $this->_initOrder())) {
            return;
        }
        try {
            $order->getPayment()->void(new \Magento\Framework\Object()); // workaround for backwards compatibility
            $order->save();
            $this->messageManager->addSuccess(__('The payment has been voided.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t void the payment.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $this->_redirect('sales/*/view', ['order_id' => $order->getId()]);
    }
}
