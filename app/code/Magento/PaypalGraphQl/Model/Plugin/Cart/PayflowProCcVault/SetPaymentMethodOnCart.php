<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Plugin\Cart\PayflowProCcVault;

use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderPool;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Set additionalInformation on payment for PayflowPro vault method
 */
class SetPaymentMethodOnCart
{
    const CC_VAULT_CODE = 'payflowpro_cc_vault';

    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var AdditionalDataProviderPool
     */
    private $additionalDataProviderPool;

    /**
     * PaymentTokenManagementInterface $paymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @param PaymentRepository $paymentRepository
     * @param AdditionalDataProviderPool $additionalDataProviderPool
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     */
    public function __construct(
        PaymentRepository $paymentRepository,
        AdditionalDataProviderPool $additionalDataProviderPool,
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->additionalDataProviderPool = $additionalDataProviderPool;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Set public hash and customer id on payment additionalInformation
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject
     * @param mixed $result
     * @param Quote $cart
     * @param array $additionalData
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute(
        \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject,
        $result,
        Quote $cart,
        array $additionalData
    ): void {
        $additionalData = $this->additionalDataProviderPool->getData(self::CC_VAULT_CODE, $additionalData);
        $customerId = (int) $cart->getCustomer()->getId();
        $payment = $cart->getPayment();
        if (!is_array($additionalData)
            || !isset($additionalData[PaymentTokenInterface::PUBLIC_HASH])
            || $customerId === 0
        ) {
            return;
        }
        $tokenPublicHash = $additionalData[PaymentTokenInterface::PUBLIC_HASH];
        if ($tokenPublicHash === null) {
            return;
        }
        $paymentToken = $this->paymentTokenManagement->getByPublicHash($tokenPublicHash, $customerId);
        if ($paymentToken === null) {
            return;
        }
        $payment->setAdditionalInformation(
            [
                PaymentTokenInterface::CUSTOMER_ID => $customerId,
                PaymentTokenInterface::PUBLIC_HASH => $tokenPublicHash
            ]
        );
        $payment->save();
    }
}
