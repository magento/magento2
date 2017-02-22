<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * PayPal module observer
 */
class AddBillingAgreementToSessionObserver implements ObserverInterface
{
    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     */
    protected $agreementFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->agreementFactory = $agreementFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $orderPayment = $observer->getEvent()->getPayment();
        $agreementCreated = false;
        if ($orderPayment->getBillingAgreementData()) {
            $order = $orderPayment->getOrder();
            /** @var \Magento\Paypal\Model\Billing\Agreement $agreement */
            $agreement = $this->agreementFactory->create()->importOrderPayment($orderPayment);
            if ($agreement->isValid()) {
                $message = __('Created billing agreement #%1.', $agreement->getReferenceId());
                $order->addRelatedObject($agreement);
                $agreement->addOrderRelation($order);
                $this->checkoutSession->setLastBillingAgreementReferenceId($agreement->getReferenceId());
                $agreementCreated = true;
            } else {
                $message = __('We can\'t create a billing agreement for this order.');
            }
            $comment = $order->addStatusHistoryComment($message);
            $order->addRelatedObject($comment);
        }
        if (!$agreementCreated) {
            $this->checkoutSession->unsLastBillingAgreementReferenceId();
        }
    }
}
