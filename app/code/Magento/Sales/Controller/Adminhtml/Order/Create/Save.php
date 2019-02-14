<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\PaymentException;

class Save extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Saving quote and create order
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // check if the creation of a new customer is allowed
            if (!$this->_authorization->isAllowed('Magento_Customer::manage')
                && !$this->_getSession()->getCustomerId()
                && !$this->_getSession()->getQuote()->getCustomerIsGuest()
            ) {
                return $this->resultForwardFactory->create()->forward('denied');
            }
            $this->_getOrderCreateModel()->getQuote()->setCustomerId($this->_getSession()->getCustomerId());
            $this->_processActionData('save');
            $paymentData = $this->getRequest()->getPost('payment');
            if ($paymentData) {
                $paymentData['checks'] = [
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_INTERNAL,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
                ];
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();

            $this->_getSession()->clearStorage();
            $this->messageManager->addSuccessMessage(__('You created the order.'));
            if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
                $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            } else {
                $resultRedirect->setPath('sales/order/index');
            }
        } catch (PaymentException $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
            $resultRedirect->setPath('sales/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addErrorMessage($message);
            }
            $resultRedirect->setPath('sales/*/');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Order saving error: %1', $e->getMessage()));
            $resultRedirect->setPath('sales/*/');
        }

        return $resultRedirect;
    }
}
