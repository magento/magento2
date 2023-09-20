<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

/**
 * Class responsive for sending order emails when it's created through storefront.
 */
class SubmitObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @param LoggerInterface $logger
     * @param OrderSender $orderSender
     */
    public function __construct(
        LoggerInterface $logger,
        OrderSender $orderSender
    ) {
        $this->logger = $logger;
        $this->orderSender = $orderSender;
    }

    /**
     * Send order email.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var  Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();

        /**
         * a flag to set that there will be redirect to third party after confirmation
         */
        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
        if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }
    }
}
