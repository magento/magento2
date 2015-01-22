<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class MassCancel extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Cancel selected orders
     *
     * @return void
     */
    public function execute()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', []);
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            if ($order->canCancel()) {
                $order->cancel()->save();
                $countCancelOrder++;
            } else {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->messageManager->addError(__('%1 order(s) cannot be canceled.', $countNonCancelOrder));
            } else {
                $this->messageManager->addError(__('You cannot cancel the order(s).'));
            }
        }
        if ($countCancelOrder) {
            $this->messageManager->addSuccess(__('We canceled %1 order(s).', $countCancelOrder));
        }
        $this->_redirect('sales/*/');
    }
}
