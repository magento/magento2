<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


/**
 * Class Email
 *
 * @package Magento\Sales\Controller\Adminhtml\Order
 */
class Email extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Notify user
     *
     * @return void
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $this->_objectManager->create('Magento\Sales\Model\OrderNotifier')
                    ->notify($order);
                $this->messageManager->addSuccess(__('You sent the order email.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We couldn\'t send the email order.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
            $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
        }
    }
}
