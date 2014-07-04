<?php
/**
 * Adminhtml DirectPost Payment Controller
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost;

class Payment extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $productHelper);
    }

    /**
     * Get session model
     *
     * @return \Magento\Authorizenet\Model\Directpost\Session
     */
    protected function _getDirectPostSession()
    {
        return $this->_objectManager->get('Magento\Authorizenet\Model\Directpost\Session');
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getOrderSession()
    {
        return $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
    }

    /**
     * Retrieve order create model
     *
     * @return \Magento\Sales\Model\AdminOrder\Create
     */
    protected function _getOrderCreateModel()
    {
        return $this->_objectManager->get('Magento\Sales\Model\AdminOrder\Create');
    }

    /**
     * Send request to authorize.net
     *
     * @return void
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
            $sendConfirmationFlag = !empty($orderData['send_confirmation']) ? 1 : 0;
        } else {
            $orderData = array();
        }

        if (isset($paymentParam['method'])) {
            $result = array();
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
                    $session = $this->_getDirectPostSession();
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
                    $result['directpost'] = array('fields' => $requestToAuthorizenet->getData());
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
            $result = array('error_messages' => __('Please choose a payment method.'));
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Retrieve params and put javascript into iframe
     *
     * @return void
     */
    public function redirectAction()
    {
        $redirectParams = $this->getRequest()->getParams();
        $params = array();
        if (!empty($redirectParams['success']) && isset(
            $redirectParams['x_invoice_num']
        ) && isset(
            $redirectParams['controller_action_name']
        )
        ) {
            $params['redirect_parent'] = $this->_objectManager->get(
                'Magento\Authorizenet\Helper\HelperInterface'
            )->getSuccessOrderUrl(
                $redirectParams
            );
            $this->_getDirectPostSession()->unsetData('quote_id');
            //cancel old order
            $oldOrder = $this->_getOrderCreateModel()->getSession()->getOrder();
            if ($oldOrder->getId()) {
                /* @var $order \Magento\Sales\Model\Order */
                $order = $this->_objectManager->create(
                    'Magento\Sales\Model\Order'
                )->loadByIncrementId(
                    $redirectParams['x_invoice_num']
                );
                if ($order->getId()) {
                    $oldOrder->cancel()->save();
                    $order->save();
                    $this->_getOrderCreateModel()->getSession()->unsOrderId();
                }
            }
            //clear sessions
            $this->_getSession()->clearStorage();
            $this->_getDirectPostSession()->removeCheckoutOrderIncrementId($redirectParams['x_invoice_num']);
            $this->_objectManager->get('Magento\Backend\Model\Session')->clearStorage();
            $this->messageManager->addSuccess(__('You created the order.'));
        }

        if (!empty($redirectParams['error_msg'])) {
            $cancelOrder = empty($redirectParams['x_invoice_num']);
            $this->_returnQuote($cancelOrder, $redirectParams['error_msg']);
        }

        $this->_coreRegistry->register('authorizenet_directpost_form_params', array_merge($params, $redirectParams));
        $this->_view->loadLayout(false)->renderLayout();
    }

    /**
     * Return order quote by ajax
     *
     * @return void
     */
    public function returnQuoteAction()
    {
        $this->_returnQuote();
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array('success' => 1))
        );
    }

    /**
     * Return quote
     *
     * @param bool $cancelOrder
     * @param string $errorMsg
     * @return void
     */
    protected function _returnQuote($cancelOrder = false, $errorMsg = '')
    {
        $incrementId = $this->_getDirectPostSession()->getLastOrderIncrementId();
        if ($incrementId && $this->_getDirectPostSession()->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            if ($order->getId()) {
                $this->_getDirectPostSession()->removeCheckoutOrderIncrementId($order->getIncrementId());
                if ($cancelOrder && $order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                    $order->registerCancellation($errorMsg)->save();
                }
            }
        }
    }
}
