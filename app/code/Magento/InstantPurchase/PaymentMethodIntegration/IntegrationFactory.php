<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Payment method integration facade factory.
 */
class IntegrationFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * IntegrationFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates instance of integration facade.
     *
     * @param VaultPaymentInterface $paymentMethod
     * @param int $storeId
     * @return Integration
     */
    public function create(VaultPaymentInterface $paymentMethod, int $storeId): Integration
    {
        $config = $paymentMethod->getConfigData('instant_purchase', $storeId);
        $availabilityChecker = $this->extractFromConfig(
            $config,
            'available',
            AvailabilityCheckerInterface::class
        );
        $paymentTokenFormatter = $this->extractFromConfig(
            $config,
            'tokenFormat',
            PaymentTokenFormatterInterface::class
        );
        $paymentAdditionalInformationProvider = $this->extractFromConfig(
            $config,
            'additionalInformation',
            PaymentAdditionalInformationProviderInterface::class
        );

        $integration = $this->objectManager->create(Integration::class, [
            'vaultPaymentMethod' => $paymentMethod,
            'availabilityChecker' => $this->objectManager->get($availabilityChecker),
            'paymentTokenFormatter' => $this->objectManager->get($paymentTokenFormatter),
            'paymentAdditionalInformationProvider' => $this->objectManager->get($paymentAdditionalInformationProvider),
        ]);
        return $integration;
    }

    /**
     * Reads value from config.
     *
     * @param array $config
     * @param string $field
     * @param string $default
     * @return string
     */
    private function extractFromConfig($config, string $field, string $default): string
    {
        return $config[$field] ?? $default;
    }
}
