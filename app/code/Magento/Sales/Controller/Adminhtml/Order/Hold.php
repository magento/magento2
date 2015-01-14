<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class Hold extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Hold order
     *
     * @return void
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $order->hold()->save();
                $this->messageManager->addSuccess(__('You put the order on hold.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('You have not put the order on hold.'));
            }
            $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
        }
    }
}
