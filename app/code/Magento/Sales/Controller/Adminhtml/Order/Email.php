<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Sales\Controller\Adminhtml\Order;

class Email extends Order implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::email';

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
                $isEnabled = (bool)$order->getStore()->getConfig('sales_email/order/enabled');
                if (!$isEnabled) {
                    $this->messageManager->addWarningMessage(
                        __('Order emails are disabled for this store. No email was sent.')
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        'sales/order/view',
                        ['order_id' => $order->getEntityId()]
                    );
                }
                $this->orderManagement->notify($order->getEntityId());
                $this->messageManager->addSuccessMessage(__('You sent the order email.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t send the email order right now.'));
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
