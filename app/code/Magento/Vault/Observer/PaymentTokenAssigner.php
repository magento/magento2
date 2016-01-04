<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Model\Quote\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Method\Vault;
use Magento\Vault\Model\PaymentTokenManagement;

class PaymentTokenAssigner extends AbstractDataAssignObserver
{
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * PaymentTokenAssigner constructor.
     * @param PaymentTokenManagement $paymentTokenManagement
     */
    public function __construct(
        PaymentTokenManagement $paymentTokenManagement
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);

        $tokenPublicHash = $dataObject->getData(PaymentTokenInterface::PUBLIC_HASH);

        if ($tokenPublicHash === null) {
            return;
        }

        /** @var Payment $paymentModel */
        $paymentModel = $this->readPaymentModelArgument($observer);
        if (!$paymentModel instanceof Payment) {
            return;
        }

        $quote = $paymentModel->getQuote();
        $customerId = $quote->getCustomerId();
        if ($customerId === null) {
            return;
        }

        $paymentToken = $this->paymentTokenManagement->getByPublicHash($tokenPublicHash, $customerId);
        if ($paymentToken === null) {
            return;
        }

        $paymentModel->setAdditionalInformation(
            Vault::TOKEN_METADATA_KEY,
            [
                PaymentTokenInterface::CUSTOMER_ID => $customerId,
                PaymentTokenInterface::PUBLIC_HASH => $tokenPublicHash
            ]
        );
    }
}
