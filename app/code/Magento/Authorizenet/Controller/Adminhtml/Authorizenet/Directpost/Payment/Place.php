<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

class Place extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Send request to authorize.net
     *
     * @return void
     */
    public function execute()
    {
        $paymentParam = $this->getRequest()->getParam('payment');
        $controller = $this->getRequest()->getParam('controller');
        $this->getRequest()->setPost('collect_shipping_rates', 1);
        $this->_processActionData('save');

        //get confirmation by email flag
        $orderData = $this->getRequest()->getPost('order');
        $sendConfirmationFlag = 0;
        if ($orderData) {
            $sendConfirmationFlag = !empty($orderData['send_confirmation']) ? 1 : 0;
        } else {
            $orderData = [];
        }

        if (isset($paymentParam['method'])) {
            $result = [];
            $params = $this->_objectManager->get(
                'Magento\Authorizenet\Helper\Data'
            )->getSaveOrderUrlParams(
                $controller
            );
            //create order partially
            $this->_getOrderCreateModel()->setPaymentData($paymentParam);
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentParam);

            $orderData['send_confirmation'] = 0;
            $this->getRequest()->setPost('order', $orderData);

            try {
                //do not cancel old order.
                $oldOrder = $this->_getOrderCreateModel()->getSession()->getOrder();
                $oldOrder->setActionFlag(\Magento\Sales\Model\Order::ACTION_FLAG_CANCEL, false);

                $order = $this->_getOrderCreateModel()->setIsValidate(
                    true
                )->importPostData(
                    $this->getRequest()->getPost('order')
                )->createOrder();

                $payment = $order->getPayment();
                if ($payment && $payment->getMethod() == $this->_objectManager->create(
                    'Magento\Authorizenet\Model\Directpost'
                )->getCode()
                ) {
                    //return json with data.
                    $session = $this->_objectManager->get('Magento\Authorizenet\Model\Directpost\Session');
                    $session->addCheckoutOrderIncrementId($order->getIncrementId());
                    $session->setLastOrderIncrementId($order->getIncrementId());

                    $requestToAuthorizenet = $payment->getMethodInstance()->generateRequestFromOrder($order);
                    $requestToAuthorizenet->setControllerActionName($controller);
                    $requestToAuthorizenet->setOrderSendConfirmation($sendConfirmationFlag);
                    $requestToAuthorizenet->setStoreId($this->_getOrderCreateModel()->getQuote()->getStoreId());

                    $adminUrl = $this->_objectManager->get('Magento\Backend\Model\UrlInterface');
                    if ($adminUrl->useSecretKey()) {
                        $requestToAuthorizenet->setKey(
                            $adminUrl->getSecretKey('adminhtml', 'authorizenet_directpost_payment', 'redirect')
                        );
                    }
                    $result['directpost'] = ['fields' => $requestToAuthorizenet->getData()];
                }

                $result['success'] = 1;
                $isError = false;
            } catch (\Magento\Framework\Model\Exception $e) {
                $message = $e->getMessage();
                if (!empty($message)) {
                    $this->messageManager->addError($message);
                }
                $isError = true;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Order saving error: %1', $e->getMessage()));
                $isError = true;
            }

            if ($isError) {
                $result['success'] = 0;
                $result['error'] = 1;
                $result['redirect'] = $this->_objectManager->get(
                    'Magento\Backend\Model\UrlInterface'
                )->getUrl(
                    'sales/order_create/'
                );
            }

            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        } else {
            $result = ['error_messages' => __('Please choose a payment method.')];
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }
}
