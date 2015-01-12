<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class Unhold extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Unhold order
     *
     * @return void
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $order->unhold()->save();
                $this->messageManager->addSuccess(__('You released the order from holding status.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('The order was not on hold.'));
            }
            $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
        }
    }
}
