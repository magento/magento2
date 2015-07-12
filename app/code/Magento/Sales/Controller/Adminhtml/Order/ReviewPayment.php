<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class ReviewPayment extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Manage payment state
     *
     * Either denies or approves a payment that is in "review" state
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
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
                        } else if ($order->getPayment()->getIsTransactionDenied()) {
                            $message = __('Transaction has been voided/declined.');
                        } else {
                            $message = __('There is no update for the transaction.');
                        }
                        break;
                    default:
                        throw new \Exception(sprintf('Action "%s" is not supported.', $action));
                }
                $order->save();
                $this->messageManager->addSuccess($message);
            } else {
                $resultRedirect->setPath('sales/*/');
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t update the payment right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::review_payment');
    }
}
