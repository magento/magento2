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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ogone\Controller;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Ogone Api Controller
 */
class Api extends \Magento\Framework\App\Action\Action
{
    /**
     * Order instance
     *
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_salesOrderFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param OrderSender $orderSender
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        OrderSender $orderSender
    ) {
        parent::__construct($context);
        $this->_transactionFactory = $transactionFactory;
        $this->_salesOrderFactory = $salesOrderFactory;
        $this->orderSender = $orderSender;
    }

    /**
     * Get checkout session namespace
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Get singleton with Checkout by Ogone Api
     *
     * @return \Magento\Ogone\Model\Api
     */
    protected function _getApi()
    {
        return $this->_objectManager->get('Magento\Ogone\Model\Api');
    }

    /**
     * Return order instance loaded by increment id
     *
     * @return Order
     */
    protected function _getOrder()
    {
        if (empty($this->_order)) {
            $orderId = $this->getRequest()->getParam('orderID');
            $this->_order = $this->_salesOrderFactory->create()->loadByIncrementId($orderId);
        }
        return $this->_order;
    }

    /**
     * Validation of incoming Ogone data
     *
     * @return bool
     */
    protected function _validateOgoneData()
    {
        $params = $this->getRequest()->getParams();
        $api = $this->_getApi();
        $api->debugData(array('result' => $params));

        $hashValidationResult = false;
        if ($api->getConfig()->getShaInCode()) {
            $referenceHash = $api->getHash(
                $params,
                $api->getConfig()->getShaInCode(),
                \Magento\Ogone\Model\Api::HASH_DIR_IN,
                (int)$api->getConfig()->getConfigData('shamode'),
                $api->getConfig()->getConfigData('hashing_algorithm')
            );
            if ($params['SHASIGN'] == $referenceHash) {
                $hashValidationResult = true;
            }
        }

        if (!$hashValidationResult) {
            $this->messageManager->addError(__('The hash is not valid.'));
            return false;
        }

        $order = $this->_getOrder();
        if (!$order->getId()) {
            $this->messageManager->addError(__('The order is not valid.'));
            return false;
        }

        return true;
    }

    /**
     * Made offline Ogone data processing, depending of incoming statuses
     *
     * @return void
     */
    protected function _ogoneProcess()
    {
        $status = $this->getRequest()->getParam('STATUS');
        switch ($status) {
            case \Magento\Ogone\Model\Api::OGONE_AUTHORIZED:
            case \Magento\Ogone\Model\Api::OGONE_AUTH_PROCESSING:
            case \Magento\Ogone\Model\Api::OGONE_PAYMENT_REQUESTED_STATUS:
                $this->_acceptProcess();
                break;
            case \Magento\Ogone\Model\Api::OGONE_AUTH_REFUZED:
            case \Magento\Ogone\Model\Api::OGONE_PAYMENT_INCOMPLETE:
            case \Magento\Ogone\Model\Api::OGONE_TECH_PROBLEM:
                $this->_declineProcess();
                break;
            case \Magento\Ogone\Model\Api::OGONE_AUTH_UKNKOWN_STATUS:
            case \Magento\Ogone\Model\Api::OGONE_PAYMENT_UNCERTAIN_STATUS:
                $this->_exceptionProcess();
                break;
            default:
                //all unknown transaction will accept as exceptional
                $this->_exceptionProcess();
                break;
        }
    }

    /**
     * Process success action by accept url
     *
     * @return void
     */
    protected function _acceptProcess()
    {
        $params = $this->getRequest()->getParams();
        $order = $this->_getOrder();
        if (!$this->_isOrderValid($order)) {
            return;
        }

        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());

        $this->_prepareCCInfo($order, $params);
        $order->getPayment()->setTransactionId($params['PAYID']);
        $order->getPayment()->setLastTransId($params['PAYID']);

        try {
            $status = $this->getRequest()->getParam('STATUS');
            switch ($status) {
                case \Magento\Ogone\Model\Api::OGONE_AUTHORIZED:
                case \Magento\Ogone\Model\Api::OGONE_AUTH_PROCESSING:
                    $this->_processAuthorize();
                    break;
                case \Magento\Ogone\Model\Api::OGONE_PAYMENT_REQUESTED_STATUS:
                    $this->_processDirectSale();
                    break;
                default:
                    throw new \Exception(__('Can\'t detect Ogone payment action'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('The order cannot be saved.'));
            $this->_redirect('checkout/cart');
            return;
        }
    }

    /**
     * Process Configured Payment Action: Direct Sale, create invoice if state is Pending
     *
     * @return void
     */
    protected function _processDirectSale()
    {
        $order = $this->_getOrder();
        $status = $this->getRequest()->getParam('STATUS');
        try {
            if ($status == \Magento\Ogone\Model\Api::OGONE_AUTH_PROCESSING) {
                $order->setState(
                    \Magento\Sales\Model\Order::STATE_PROCESSING,
                    \Magento\Ogone\Model\Api::WAITING_AUTHORIZATION,
                    __('Authorization Waiting from Ogone')
                );
                $order->save();
            } elseif ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                if ($status == \Magento\Ogone\Model\Api::OGONE_AUTHORIZED) {
                    if ($order->getStatus() != \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                        $order->setState(
                            \Magento\Sales\Model\Order::STATE_PROCESSING,
                            \Magento\Ogone\Model\Api::PROCESSING_OGONE_STATUS,
                            __('Processed by Ogone')
                        );
                    }
                } else {
                    $order->setState(
                        \Magento\Sales\Model\Order::STATE_PROCESSING,
                        \Magento\Ogone\Model\Api::PROCESSED_OGONE_STATUS,
                        __('Processed by Ogone')
                    );
                }

                $order->getPayment()->addTransaction(PaymentTransaction::TYPE_CAPTURE);

                if (!$order->getInvoiceCollection()->getSize()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->register();
                    $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
                    $invoice->getOrder()->setIsInProcess(true);

                    $this->_transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder())->save();
                    $this->orderSender->send($order);
                }
            } else {
                $order->save();
            }
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Order can\'t save'));
            $this->_redirect('checkout/cart');
            return;
        }
    }

    /**
     * Process Configured Payment Actions: Authorized, Default operation
     * just place order
     *
     * @return void
     */
    protected function _processAuthorize()
    {
        $order = $this->_getOrder();
        $status = $this->getRequest()->getParam('STATUS');
        try {
            if ($status == \Magento\Ogone\Model\Api::OGONE_AUTH_PROCESSING) {
                $order->setState(
                    \Magento\Sales\Model\Order::STATE_PROCESSING,
                    \Magento\Ogone\Model\Api::WAITING_AUTHORIZATION,
                    __('Authorization Waiting from Ogone')
                );
            } else {
                //to send new order email only when state is pending payment
                if ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                    $this->orderSender->send($order);
                }
                $order->setState(
                    \Magento\Sales\Model\Order::STATE_PROCESSING,
                    \Magento\Ogone\Model\Api::PROCESSED_OGONE_STATUS,
                    __('Processed by Ogone')
                );

                $order->getPayment()->addTransaction(PaymentTransaction::TYPE_AUTH);
            }
            $order->save();
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Order can\'t save'));
            $this->_redirect('checkout/cart');
            return;
        }
    }

    /**
     * We get some CC info from Ogone, so we must save it
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $ccInfo
     *
     * @return $this
     */
    protected function _prepareCCInfo($order, $ccInfo)
    {
        $order->getPayment()->setCcOwner($ccInfo['CN']);
        $order->getPayment()->setCcNumberEnc($ccInfo['CARDNO']);
        $order->getPayment()->setCcLast4(substr($ccInfo['CARDNO'], -4));
        $order->getPayment()->setCcExpMonth(substr($ccInfo['ED'], 0, 2));
        $order->getPayment()->setCcExpYear(substr($ccInfo['ED'], 2, 2));
        return $this;
    }

    /**
     * Process exception action by Ogone exception url
     *
     * @return void
     */
    public function _exceptionProcess()
    {
        $params = $this->getRequest()->getParams();
        $order = $this->_getOrder();
        if (!$this->_isOrderValid($order)) {
            return;
        }

        switch ($params['STATUS']) {
            case \Magento\Ogone\Model\Api::OGONE_PAYMENT_UNCERTAIN_STATUS:
                $exception = __(
                    'Something went wrong during the payment process, and so the result is unpredictable.'
                );
                break;
            case \Magento\Ogone\Model\Api::OGONE_AUTH_UKNKOWN_STATUS:
                $exception = __(
                    'Something went wrong during the authorization process, and so the result is unpredictable.'
                );
                break;
            default:
                $exception = __('Unknown exception');
                break;
        }

        if (!empty($exception)) {
            try {
                $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
                $this->_prepareCCInfo($order, $params);
                $order->getPayment()->setLastTransId($params['PAYID']);
                //to send new order email only when state is pending payment
                if ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                    $this->orderSender->send($order);
                    $order->setState(
                        \Magento\Sales\Model\Order::STATE_PROCESSING,
                        \Magento\Ogone\Model\Api::PROCESSING_OGONE_STATUS,
                        $exception
                    );
                } else {
                    $order->addStatusToHistory(\Magento\Ogone\Model\Api::PROCESSING_OGONE_STATUS, $exception);
                }
                $order->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving this order.'));
            }
        } else {
            $this->messageManager->addError(__('Exception not defined'));
        }

        $this->_redirect('checkout/onepage/success');
    }

    /**
     * Process decline action by Ogone decline url
     *
     * @return void
     */
    protected function _declineProcess()
    {
        $status = \Magento\Ogone\Model\Api::DECLINE_OGONE_STATUS;
        $comment = __('Declined Order on Ogone side');
        $this->messageManager->addError(__('The payment transaction has been declined.'));
        $this->_cancelOrder($status, $comment);
    }

    /**
     * Cancel action, used for decline and cancel processes
     *
     * @param string $status
     * @param string $comment
     * @return void|$this
     */
    protected function _cancelOrder($status, $comment = '')
    {
        $order = $this->_getOrder();
        if (!$this->_isOrderValid($order)) {
            return;
        }

        try {
            $order->cancel();
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, $status, $comment);
            $order->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong while canceling this order.'));
        }

        $this->_redirect('checkout/cart');
        return $this;
    }

    /**
     * Check order payment method
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function _isOrderValid($order)
    {
        return \Magento\Ogone\Model\Api::PAYMENT_CODE == $order->getPayment()->getMethodInstance()->getCode();
    }
}
