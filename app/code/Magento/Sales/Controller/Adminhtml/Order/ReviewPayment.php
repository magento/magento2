<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\ReviewPayment
 */
class ReviewPayment extends \Magento\Sales\Controller\Adminhtml\Order implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::review_payment';

    /**
     * Manage payment state
     *
     * Either denies or approves a payment that is in "review" state
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $order = $this->_initOrder();
            if ($order) {
                $action = $this->getRequest()->getParam('action', '');
                switch ($action) {
                    case 'accept':
                        $order->getPayment()->accept();
                        $message = __('The payment has been accepted.');
                        break;
                    case 'deny':
                        $order->getPayment()->deny();
                        $message = __('The payment has been denied.');
                        break;
                    case 'update':
                        $order->getPayment()->update();
                        if ($order->getPayment()->getIsTransactionApproved()) {
                            $message = __('Transaction has been approved.');
                        } elseif ($order->getPayment()->getIsTransactionDenied()) {
                            $message = __('Transaction has been voided/declined.');
                        } else {
                            $message = __('There is no update for the transaction.');
                        }
                        break;
                    default:
                        throw new \Magento\Framework\Exception\NotFoundException(
                            __('Action "%1" is not supported.', $action)
                        );
                }
                $this->orderRepository->save($order);
                $this->messageManager->addSuccessMessage($message);
                $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getEntityId()]);
            } else {
                $resultRedirect->setPath('sales/*/');
                return $resultRedirect;
            }
            // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('We can\'t update the payment right now.'));
            $this->logger->critical($e);
            $resultRedirect->setPath('sales/*/');
        }
        return $resultRedirect;
    }
}
