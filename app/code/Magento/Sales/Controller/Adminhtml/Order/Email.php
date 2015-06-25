<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class Email extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $this->_objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
                $this->messageManager->addSuccess(__('You sent the order email.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t send the email order right now.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
            return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $order->getId()]);
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::email');
    }
}
