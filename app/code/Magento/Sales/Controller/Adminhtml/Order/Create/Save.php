<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;


class Save extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Saving quote and create order
     *
     * @return void
     */
    public function execute()
    {
        try {
            // check if the creation of a new customer is allowed
            if (!$this->_authorization->isAllowed('Magento_Customer::manage')
                && !$this->_getSession()->getCustomerId()
                && !$this->_getSession()->getQuote()->getCustomerIsGuest()
            ) {
                $this->_forward('denied');
                return;
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

            $order = $this->_getOrderCreateModel()->setIsValidate(
                true
            )->importPostData(
                $this->getRequest()->getPost('order')
            )->createOrder();

            $this->_getSession()->clearStorage();
            $this->messageManager->addSuccess(__('You created the order.'));
            if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
                $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
            } else {
                $this->_redirect('sales/order/index');
            }
        } catch (\Magento\Payment\Model\Info\Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $this->_redirect('sales/*/');
        } catch (\Magento\Framework\Model\Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $this->_redirect('sales/*/');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Order saving error: %1', $e->getMessage()));
            $this->_redirect('sales/*/');
        }
    }
}
