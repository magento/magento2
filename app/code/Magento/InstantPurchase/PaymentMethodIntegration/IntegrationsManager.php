<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentMethodList;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Payment method integrations management.
 *
 * Avoid usage of methods for automatic store detection if possible.
 */
class IntegrationsManager
{
    /**
     * @var PaymentMethodList
     */
    private $paymentMethodList;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $integrations;

    /**
     * IntegrationsManager constructor.
     * @param PaymentMethodList $paymentMethodList
     * @param IntegrationFactory $integrationFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PaymentMethodList $paymentMethodList,
        IntegrationFactory $integrationFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->paymentMethodList = $paymentMethodList;
        $this->integrationFactory = $integrationFactory;
        $this->storeManager = $storeManager;
        $this->integrations = [];
    }

    /**
     * Provides list of implemented integrations.
     *
     * @param int $storeId
     * @return array
     */
    public function getList(int $storeId): array
    {
        if (!isset($this->integrations[$storeId])) {
            $this->integrations[$storeId] = $this->findIntegrations($storeId);
        }
        return $this->integrations[$storeId];
    }

    /**
     * Determines integration that may be used with token.
     *
     * @param PaymentTokenInterface $paymentToken
     * @param int $storeId
     * @return Integration
     * @throws LocalizedException if integration is not implemented.
     */
    public function getByToken(PaymentTokenInterface $paymentToken, int $storeId): Integration
    {
        foreach ($this->getList($storeId) as $integration) {
            if ($integration->getVaultProviderCode() === $paymentToken->getPaymentMethodCode()) {
                return $integration;
            }
        }
        throw new LocalizedException(__('Instant purchase integration not available for token.'));
    }

    /**
     * Provides list of implemented integrations with store detection.
     * This method rely on global state. Use getList if possible.
     *
     * @return array
     */
    public function getListForCurrentStore(): array
    {
        return $this->getList($this->storeManager->getStore()->getId());
    }

    /**
     * Determines integration that may be used with token with store detection.
     * This method rely on global state. Use getByToken if possible.
     *
     * @param PaymentTokenInterface $paymentToken
     * @return Integration
     * @throws LocalizedException if integration is not implemented.
     */
    public function getByTokenForCurrentStore(PaymentTokenInterface $paymentToken): Integration
    {
        return $this->getByToken($paymentToken, $this->storeManager->getStore()->getId());
    }

    /**
     * Find implemented integrations for active vault payment methods.
     *
     * @param int $storeId
     * @return array
     */
    private function findIntegrations(int $storeId): array
    {
        $integrations = [];
        foreach ($this->paymentMethodList->getActiveList($storeId) as $paymentMethod) {
            if ($this->isIntegrationAvailable($paymentMethod, $storeId)) {
                $integrations[] = $this->integrationFactory->create($paymentMethod, $storeId);
            }
        }
        return $integrations;
    }

    /**
     * Checks if integration implemented for vault payment method.
     *
     * Implemented integration is if it configured in payment config:
     * 1. Basic integration (not recommended):
     *    <instant_purchase>
     *      <supported>1</supported>
     *    </instant_purchase>
     * 2. Customized integration (at least one option is required):
     *    <instant_purchase>
     *       <available>Implementation_Of_AvailabilityCheckerInterface</available>
     *       <tokenFormat>Implementation_Of_PaymentTokenFormatterInterface</tokenFormat>
     *       <additionalInformation>
     *           Implementation_Of_PaymentAdditionalInformationProviderInterface
     *       </additionalInformation>
     *    </instant_purchase>
     *
     * @param VaultPaymentInterface $paymentMethod
     * @param $storeId
     * @return bool
     */
    private function isIntegrationAvailable(VaultPaymentInterface $paymentMethod, $storeId): bool
    {
        $data = $paymentMethod->getConfigData('instant_purchase', $storeId);
        if (!is_array($data)) {
            return false;
        }
        if (isset($data['supported']) && $data['supported'] !== '1') {
            return false;
        }
        return true;
    }
}
