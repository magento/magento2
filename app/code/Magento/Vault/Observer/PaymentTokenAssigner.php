<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

/**
 * Class \Magento\Vault\Observer\PaymentTokenAssigner
 *
 * @since 2.1.0
 */
class PaymentTokenAssigner extends AbstractDataAssignObserver
{
    /**
     * @var PaymentTokenManagementInterface
     * @since 2.1.0
     */
    private $paymentTokenManagement;

    /**
     * PaymentTokenAssigner constructor.
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @since 2.1.0
     */
    public function __construct(
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @param Observer $observer
     * @return void
     * @since 2.1.0
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
