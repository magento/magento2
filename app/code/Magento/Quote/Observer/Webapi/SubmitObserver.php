<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Webapi;

use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Event\ObserverInterface;

class SubmitObserver implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderSender $orderSender
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderSender $orderSender
    ) {
        $this->logger = $logger;
        $this->orderSender = $orderSender;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        /**
         * a flag to set that there will be redirect to third party after confirmation
         */
        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
        if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
