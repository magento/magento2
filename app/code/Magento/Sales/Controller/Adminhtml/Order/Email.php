<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Email
 *
 * @since 2.0.0
 */
class Email extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::email';

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $this->orderManagement->notify($order->getEntityId());
                $this->messageManager->addSuccess(__('You sent the order email.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t send the email order right now.'));
                $this->logger->critical($e);
            }
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order->getEntityId()
                ]
            );
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}
