<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller;

use Magento\Sales\Api\PaymentFailuresInterface;

/**
 * Payflow Checkout Controller
 */
abstract class Payflow extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Paypal\Model\PayflowlinkFactory
     */
    protected $_payflowModelFactory;

    /**
     * @var \Magento\Paypal\Helper\Checkout
     */
    protected $_checkoutHelper;

    /**
     * Redirect block name
     * @var string
     */
    protected $_redirectBlockName = 'payflow.link.iframe';

    /**
<<<<<<< HEAD
     * @var \Magento\Sales\Api\PaymentFailuresInterface
=======
     * @var PaymentFailuresInterface
>>>>>>> upstream/2.2-develop
     */
    private $paymentFailures;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\PayflowlinkFactory $payflowModelFactory
     * @param \Magento\Paypal\Helper\Checkout $checkoutHelper
     * @param \Psr\Log\LoggerInterface $logger
<<<<<<< HEAD
     * @param \Magento\Sales\Api\PaymentFailuresInterface|null $paymentFailures
=======
     * @param PaymentFailuresInterface|null $paymentFailures
>>>>>>> upstream/2.2-develop
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\PayflowlinkFactory $payflowModelFactory,
        \Magento\Paypal\Helper\Checkout $checkoutHelper,
        \Psr\Log\LoggerInterface $logger,
<<<<<<< HEAD
        \Magento\Sales\Api\PaymentFailuresInterface $paymentFailures = null
=======
        PaymentFailuresInterface $paymentFailures = null
>>>>>>> upstream/2.2-develop
    ) {
        parent::__construct($context);

        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_logger = $logger;
        $this->_payflowModelFactory = $payflowModelFactory;
        $this->_checkoutHelper = $checkoutHelper;
<<<<<<< HEAD
        $this->paymentFailures = $paymentFailures ?: $this->_objectManager->get(
            \Magento\Sales\Api\PaymentFailuresInterface::class
        );
=======
        $this->paymentFailures = $paymentFailures ? : $this->_objectManager->get(PaymentFailuresInterface::class);
>>>>>>> upstream/2.2-develop
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return false|string
     */
    protected function _cancelPayment($errorMsg = '')
    {
        $errorMsg = trim(strip_tags($errorMsg));
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            $this->paymentFailures->handle((int)$order->getQuoteId(), $errorMsg);
        }

        $gotoSection = false;
        $this->_checkoutHelper->cancelCurrentOrder($errorMsg);
        if ($this->_checkoutSession->restoreQuote()) {
            //Redirect to payment step
            $gotoSection = 'paymentMethod';
        }

        return $gotoSection;
    }
}
