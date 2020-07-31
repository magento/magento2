<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\InstantPurchase;

use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\InstantPurchase\PaymentMethodIntegration\PaymentAdditionalInformationProviderInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Provides Braintree specific payment additional information for instant purchase.
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class PaymentAdditionalInformationProvider implements PaymentAdditionalInformationProviderInterface
{
    /**
     * @var GetPaymentNonceCommand
     */
    private $getPaymentNonceCommand;

    /**
     * PaymentAdditionalInformationProvider constructor.
     * @param GetPaymentNonceCommand $getPaymentNonceCommand
     */
    public function __construct(GetPaymentNonceCommand $getPaymentNonceCommand)
    {
        $this->getPaymentNonceCommand = $getPaymentNonceCommand;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalInformation(PaymentTokenInterface $paymentToken): array
    {
        $paymentMethodNonce = $this->getPaymentNonceCommand->execute([
            PaymentTokenInterface::CUSTOMER_ID => $paymentToken->getCustomerId(),
            PaymentTokenInterface::PUBLIC_HASH => $paymentToken->getPublicHash(),
        ])->get()['paymentMethodNonce'];

        return [
            'payment_method_nonce' => $paymentMethodNonce,
        ];
    }
}
