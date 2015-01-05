<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class MassHold extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Hold selected orders
     *
     * @return void
     */
    public function execute()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', []);
        $countHoldOrder = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            if ($order->canHold()) {
                $order->hold()->save();
                $countHoldOrder++;
            }
        }

        $countNonHoldOrder = count($orderIds) - $countHoldOrder;

        if ($countNonHoldOrder) {
            if ($countHoldOrder) {
                $this->messageManager->addError(__('%1 order(s) were not put on hold.', $countNonHoldOrder));
            } else {
                $this->messageManager->addError(__('No order(s) were put on hold.'));
            }
        }
        if ($countHoldOrder) {
            $this->messageManager->addSuccess(__('You have put %1 order(s) on hold.', $countHoldOrder));
        }

        $this->_redirect('sales/*/');
    }
}
