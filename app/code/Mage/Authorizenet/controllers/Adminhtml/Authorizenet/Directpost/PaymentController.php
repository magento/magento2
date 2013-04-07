<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Authorizenet
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'Mage/Adminhtml/controllers/Sales/Order/CreateController.php';
/**
 * Admihtml DirtectPost Payment Controller
 *
 * @category   Mage
 * @package    Mage_DirtectPost
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Authorizenet_Adminhtml_Authorizenet_Directpost_PaymentController
    extends Mage_Adminhtml_Sales_Order_CreateController
{
    /**
     * Get session model
     *
     * @return Mage_Authorizenet_Model_Directpost_Session
     */
    protected function _getDirectPostSession()
    {
        return Mage::getSingleton('Mage_Authorizenet_Model_Directpost_Session');
    }

    /**
     * Retrieve session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getOrderSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session_Quote');
    }

    /**
     * Retrieve order create model
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Sales_Order_Create');
    }

    /**
     * Send request to authorize.net
     *
     */
    public function placeAction()
    {
        $paymentParam = $this->getRequest()->getParam('payment');
        $controller = $this->getRequest()->getParam('controller');
        $this->getRequest()->setPost('collect_shipping_rates', 1);
        $this->_processActionData('save');

        //get confirmation by email flag
        $orderData = $this->getRequest()->getPost('order');
        $sendConfirmationFlag = 0;
        if ($orderData) {
            $sendConfirmationFlag = (!empty($orderData['send_confirmation'])) ? 1 : 0;
        } else {
            $orderData = array();
        }

        if (isset($paymentParam['method'])) {
            $saveOrderFlag = Mage::getStoreConfig('payment/'.$paymentParam['method'].'/create_order_before');
            $result = array();
            $params = Mage::helper('Mage_Authorizenet_Helper_Data')->getSaveOrderUrlParams($controller);
            //create order partially
            $this->_getOrderCreateModel()->setPaymentData($paymentParam);
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentParam);

            $orderData['send_confirmation'] = 0;
            $this->getRequest()->setPost('order', $orderData);

            try {
                //do not cancel old order.
                $oldOrder = $this->_getOrderCreateModel()->getSession()->getOrder();
                $oldOrder->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CANCEL, false);

                $order = $this->_getOrderCreateModel()
                    ->setIsValidate(true)
                    ->importPostData($this->getRequest()->getPost('order'))
                    ->createOrder();

                $payment = $order->getPayment();
                if ($payment && $payment->getMethod() == Mage::getModel('Mage_Authorizenet_Model_Directpost')->getCode()) {
                    //return json with data.
                    $session = $this->_getDirectPostSession();
                    $session->addCheckoutOrderIncrementId($order->getIncrementId());
                    $session->setLastOrderIncrementId($order->getIncrementId());

                    $requestToPaygate = $payment->getMethodInstance()->generateRequestFromOrder($order);
                    $requestToPaygate->setControllerActionName($controller);
                    $requestToPaygate->setOrderSendConfirmation($sendConfirmationFlag);
                    $requestToPaygate->setStoreId($this->_getOrderCreateModel()->getQuote()->getStoreId());

                    $adminUrl = Mage::getSingleton('Mage_Backend_Model_Url');
                    if ($adminUrl->useSecretKey()) {
                        $requestToPaygate->setKey(
                            $adminUrl->getSecretKey('adminhtml', 'authorizenet_directpost_payment','redirect')
                        );
                    }
                    $result['directpost'] = array('fields' => $requestToPaygate->getData());
                }

                $result['success'] = 1;
                $isError = false;
            }
            catch (Mage_Core_Exception $e) {
                $message = $e->getMessage();
                if( !empty($message) ) {
                    $this->_getSession()->addError($message);
                }
                $isError = true;
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
                $isError = true;
            }

            if ($isError) {
                $result['success'] = 0;
                $result['error'] = 1;
                $result['redirect'] = Mage::getSingleton('Mage_Backend_Model_Url')->getUrl('*/sales_order_create/');
            }

            $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result));
        }
        else {
            $result = array(
                'error_messages' => $this->__('Please, choose payment method')
            );
            $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result));
        }
    }

    /**
     * Retrieve params and put javascript into iframe
     *
     */
    public function redirectAction()
    {
        $redirectParams = $this->getRequest()->getParams();
        $params = array();
        if (!empty($redirectParams['success'])
            && isset($redirectParams['x_invoice_num'])
            && isset($redirectParams['controller_action_name'])
        ) {
            $params['redirect_parent'] = Mage::helper('Mage_Authorizenet_Helper_Data')->getSuccessOrderUrl($redirectParams);
            $this->_getDirectPostSession()->unsetData('quote_id');
            //cancel old order
            $oldOrder = $this->_getOrderCreateModel()->getSession()->getOrder();
            if ($oldOrder->getId()) {
                /* @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($redirectParams['x_invoice_num']);
                if ($order->getId()) {
                    $oldOrder->cancel()
                        ->save();
                    $order->save();
                    $this->_getOrderCreateModel()->getSession()->unsOrderId();
                }
            }
            //clear sessions
            $this->_getSession()->clear();
            $this->_getDirectPostSession()->removeCheckoutOrderIncrementId($redirectParams['x_invoice_num']);
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->clear();
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess($this->__('The order has been created.'));
        }

        if (!empty($redirectParams['error_msg'])) {
            $cancelOrder = empty($redirectParams['x_invoice_num']);
            $this->_returnQuote($cancelOrder, $redirectParams['error_msg']);
        }

        Mage::register('authorizenet_directpost_form_params', array_merge($params, $redirectParams));
        $this->loadLayout(false)->renderLayout();
    }

    /**
     * Return order quote by ajax
     *
     */
    public function returnQuoteAction()
    {
        $this->_returnQuote();
        $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array('success' => 1)));
    }

    /**
     * Return quote
     *
     * @param bool $cancelOrder
     * @param string $errorMsg
     */
    protected function _returnQuote($cancelOrder = false, $errorMsg = '')
    {
        $incrementId = $this->_getDirectPostSession()->getLastOrderIncrementId();
        if ($incrementId &&
            $this->_getDirectPostSession()
                ->isCheckoutOrderIncrementIdExist($incrementId)
        ) {
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($incrementId);
            if ($order->getId()) {
                $this->_getDirectPostSession()->removeCheckoutOrderIncrementId($order->getIncrementId());
                if ($cancelOrder && $order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                    $order->registerCancellation($errorMsg)->save();
                }
            }
        }
    }
}
