<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\Method\Vault;

class PaymentTokenAssigner extends AbstractDataAssignObserver
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * PaymentTokenAssigner constructor.
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     */
    public function __construct(
        PaymentTokenManagementInterface $paymentTokenManagement
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

        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData) || !isset($additionalData[PaymentTokenInterface::PUBLIC_HASH])) {
            return;
        }

        $tokenPublicHash = $additionalData[PaymentTokenInterface::PUBLIC_HASH];

        if ($tokenPublicHash === null) {
            return;
        }

        /** @var Payment $paymentModel */
        $paymentModel = $this->readPaymentModelArgument($observer);
        if (!$paymentModel instanceof Payment) {
            return;
        }

        $quote = $paymentModel->getQuote();
        $customerId = (int) $quote->getCustomer()->getId();

        $paymentToken = $this->paymentTokenManagement->getByPublicHash($tokenPublicHash, $customerId);
        if ($paymentToken === null) {
            return;
        }

        $paymentModel->setAdditionalInformation(
            [
                PaymentTokenInterface::CUSTOMER_ID => $customerId,
                PaymentTokenInterface::PUBLIC_HASH => $tokenPublicHash
            ]
        );
    }
}
