<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Vault payment method integration with instant purchase facade.
 */
class Integration implements
    AvailabilityCheckerInterface,
    PaymentTokenFormatterInterface,
    PaymentAdditionalInformationProviderInterface
{
    /**
     * @var VaultPaymentInterface
     */
    private $vaultPaymentMethod;

    /**
     * @var AvailabilityCheckerInterface
     */
    private $availabilityChecker;

    /**
     * @var PaymentTokenFormatterInterface
     */
    private $paymentTokenFormatter;

    /**
     * @var PaymentAdditionalInformationProviderInterface
     */
    private $paymentAdditionalInformationProvider;

    /**
     * Integration constructor.
     * @param VaultPaymentInterface $vaultPaymentMethod
     * @param AvailabilityCheckerInterface $availabilityChecker
     * @param PaymentTokenFormatterInterface $paymentTokenFormatter
     * @param PaymentAdditionalInformationProviderInterface $paymentAdditionalInformationProvider
     */
    public function __construct(
        VaultPaymentInterface $vaultPaymentMethod,
        AvailabilityCheckerInterface $availabilityChecker,
        PaymentTokenFormatterInterface $paymentTokenFormatter,
        PaymentAdditionalInformationProviderInterface $paymentAdditionalInformationProvider
    ) {
        $this->vaultPaymentMethod = $vaultPaymentMethod;
        $this->availabilityChecker = $availabilityChecker;
        $this->paymentTokenFormatter = $paymentTokenFormatter;
        $this->paymentAdditionalInformationProvider = $paymentAdditionalInformationProvider;
    }

    /**
     * Returns integrated vault payment method code.
     *
     * @return string
     */
    public function getVaultCode(): string
    {
        return $this->vaultPaymentMethod->getCode();
    }

    /**
     * Returns integrated vault payment method provider code.
     *
     * @return string
     */
    public function getVaultProviderCode(): string
    {
        return $this->vaultPaymentMethod->getProviderCode();
    }

    /**
     * Returns integrated vault payment method instance.
     *
     * @return VaultPaymentInterface
     */
    public function getPaymentMethod(): VaultPaymentInterface
    {
        return $this->vaultPaymentMethod;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return $this->availabilityChecker->isAvailable();
    }

    /**
     * @inheritdoc
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string
    {
        return $this->paymentTokenFormatter->formatPaymentToken($paymentToken);
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalInformation(PaymentTokenInterface $paymentToken): array
    {
        return $this->paymentAdditionalInformationProvider->getAdditionalInformation($paymentToken);
    }
}
