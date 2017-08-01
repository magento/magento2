<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer\Webapi;

use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Quote\Observer\Webapi\SubmitObserver
 *
 * @since 2.0.0
 */
class SubmitObserver implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var OrderSender
     * @since 2.0.0
     */
    protected $orderSender;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderSender $orderSender
     * @since 2.0.0
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
     * @since 2.0.0
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
