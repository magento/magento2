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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payflow Advanced Checkout Controller
 */
namespace Magento\Paypal\Controller;

class Payflowadvanced extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = 'Magento\Paypal\Model\Config';

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = 'Magento\Paypal\Model\Payflowadvanced';

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Paypal\Helper\Checkout
     */
    protected $_checkoutHelper;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Core\Model\Session\Generic $paypalSession
     * @param \Magento\Paypal\Helper\Checkout $checkoutHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\UrlInterface $urlBuilder,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Core\Model\Session\Generic $paypalSession,
        \Magento\Paypal\Helper\Checkout $checkoutHelper
    ) {
        $this->_logger = $context->getLogger();
        $this->_checkoutHelper = $checkoutHelper;
        parent::__construct(
            $context,
            $customerSession,
            $urlBuilder,
            $quoteFactory,
            $checkoutSession,
            $orderFactory,
            $checkoutFactory,
            $paypalSession
        );
    }

    /**
     * When a customer cancel payment from payflow gateway.
     */
    public function cancelPaymentAction()
    {
        $this->loadLayout(false);
        $gotoSection = $this->_cancelPayment();
        $redirectBlock = $this->getLayout()->getBlock('payflow.advanced.iframe');
        $redirectBlock->setGotoSection($gotoSection);
        $this->renderLayout();
    }

    /**
     * When a customer return to website from payflow gateway.
     */
    public function returnUrlAction()
    {
        $this->loadLayout(false);
        $redirectBlock = $this->getLayout()->getBlock('payflow.advanced.iframe');;

        if ($this->_checkoutSession->getLastRealOrderId()) {
            $order = $this->_orderFactory->create()->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());

            if ($order && $order->getIncrementId() == $this->_checkoutSession->getLastRealOrderId()) {
                $allowedOrderStates = array(
                    \Magento\Sales\Model\Order::STATE_PROCESSING,
                    \Magento\Sales\Model\Order::STATE_COMPLETE
                );
                if (in_array($order->getState(), $allowedOrderStates)) {
                    $this->_checkoutSession->unsLastRealOrderId();
                    $redirectBlock->setGotoSuccessPage(true);
                } else {
                    $gotoSection = $this->_cancelPayment(strval($this->getRequest()->getParam('RESPMSG')));
                    $redirectBlock->setGotoSection($gotoSection);
                    $redirectBlock->setErrorMsg(__('Your payment has been declined. Please try again.'));
                }
            }
        }

        $this->renderLayout();
    }

    /**
     * Submit transaction to Payflow getaway into iframe
     */
    public function formAction()
    {
        $this->loadLayout(false)->renderLayout();
        $html = $this->getLayout()->getBlock('payflow.advanced.iframe')->toHtml();
        $this->getResponse()->setBody($html);
    }

    /**
     * Get response from PayPal by silent post method
     */
    public function silentPostAction()
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['INVNUM'])) {
            /** @var $paymentModel \Magento\Paypal\Model\Payflowadvanced */
            $paymentModel = $this->_checkoutFactory->create($this->_checkoutType);
            try {
                $paymentModel->process($data);
            } catch (\Exception $e) {
                $this->_logger->logException($e);
            }
        }
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return bool|string
     */
    protected function _cancelPayment($errorMsg = '')
    {
        $gotoSection = false;
        $this->_checkoutHelper->cancelCurrentOrder($errorMsg);
        if ($this->_checkoutHelper->restoreQuote()) {
            $gotoSection = 'payment';
        }
        return $gotoSection;
    }
}
