<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller;

/**
 * Payflow Checkout Controller
 * @since 2.0.0
 */
abstract class Payflow extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $_orderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Paypal\Model\PayflowlinkFactory
     * @since 2.0.0
     */
    protected $_payflowModelFactory;

    /**
     * @var \Magento\Paypal\Helper\Checkout
     * @since 2.0.0
     */
    protected $_checkoutHelper;

    /**
     * Redirect block name
     * @var string
     * @since 2.0.0
     */
    protected $_redirectBlockName = 'payflow.link.iframe';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\PayflowlinkFactory $payflowModelFactory
     * @param \Magento\Paypal\Helper\Checkout $checkoutHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\PayflowlinkFactory $payflowModelFactory,
        \Magento\Paypal\Helper\Checkout $checkoutHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_logger = $logger;
        $this->_payflowModelFactory = $payflowModelFactory;
        $this->_checkoutHelper = $checkoutHelper;
        parent::__construct($context);
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return false|string
     * @since 2.0.0
     */
    protected function _cancelPayment($errorMsg = '')
    {
        $errorMsg = trim(strip_tags($errorMsg));

        $gotoSection = false;
        $this->_checkoutHelper->cancelCurrentOrder($errorMsg);
        if ($this->_checkoutSession->restoreQuote()) {
            //Redirect to payment step
            $gotoSection = 'paymentMethod';
        }

        return $gotoSection;
    }
}
