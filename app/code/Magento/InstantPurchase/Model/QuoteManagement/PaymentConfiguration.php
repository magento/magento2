<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\QuoteManagement;

use Magento\Framework\Exception\LocalizedException;
use Magento\InstantPurchase\PaymentMethodIntegration\IntegrationsManager;
use Magento\Quote\Model\Quote;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;

/**
 * Configure payment method for quote.
 *
 * @api May be used for pluginization.
 * @since 100.2.0
 */
class PaymentConfiguration
{
    const MARKER = 'instant-purchase';

    /**
     * @var IntegrationsManager
     */
    private $integrationManager;

    /**
     * PaymentConfiguration constructor.
     * @param IntegrationsManager $integrationsManager
     */
    public function __construct(
        IntegrationsManager $integrationsManager
    ) {
        $this->integrationManager = $integrationsManager;
    }

    /**
     * Sets payment method information in quote based on stored payment token.
     *
     * @param Quote $quote
     * @param PaymentTokenInterface $paymentToken
     * @return Quote
     * @throws LocalizedException if payment method can not be configured for a quote.
     * @since 100.2.0
     */
    public function configurePayment(
        Quote $quote,
        PaymentTokenInterface $paymentToken
    ): Quote {
        $paymentMethod = $this->getVaultPaymentMethodCode(
            $paymentToken,
            $quote->getStoreId()
        );

        $payment = $quote->getPayment();
        $payment->setQuote($quote);
        $payment->importData(['method' => $paymentMethod]);
        $payment->setAdditionalInformation($this->buildPaymentAdditionalInformation(
            $paymentToken,
            $quote->getStoreId()
        ));

        return $quote;
    }

    /**
     * Detects vault payment method code based on provider payment method code.
     *
     * @param PaymentTokenInterface $paymentToken
     * @param int $storeId
     * @return string
     * @throws LocalizedException if payment not available
     */
    private function getVaultPaymentMethodCode(PaymentTokenInterface $paymentToken, int $storeId): string
    {
        try {
            $integration = $this->integrationManager->getByToken($paymentToken, $storeId);
            $vaultPaymentMethodCode = $integration->getVaultCode();
            return $vaultPaymentMethodCode;
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Specified payment method is not available now.'), $e);
        }
    }

    /**
     * Builds payment additional information based on token.
     *
     * @param PaymentTokenInterface $paymentToken
     * @param int $storeId
     * @return array
     */
    private function buildPaymentAdditionalInformation(PaymentTokenInterface $paymentToken, int $storeId): array
    {
        $common = [
            PaymentTokenInterface::CUSTOMER_ID => $paymentToken->getCustomerId(),
            PaymentTokenInterface::PUBLIC_HASH => $paymentToken->getPublicHash(),
            VaultConfigProvider::IS_ACTIVE_CODE => true,

            // mark payment
            self::MARKER => 'true',
        ];

        $integration = $this->integrationManager->getByToken($paymentToken, $storeId);
        $specific = $integration->getAdditionalInformation($paymentToken);

        $additionalInformation = array_merge($common, $specific);
        return $additionalInformation;
    }
}
