<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Payment token formatter that uses payment method title as token string representation.
 */
class SimplePaymentTokenFormatter implements PaymentTokenFormatterInterface
{
    /**
     * @var IntegrationsManager
     */
    private $integrationsManager;

    /**
     * SimplePaymentTokenFormatter constructor.
     * @param IntegrationsManager $integrationsManager
     */
    public function __construct(IntegrationsManager $integrationsManager)
    {
        $this->integrationsManager = $integrationsManager;
    }

    /**
     * @inheritdoc
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string
    {
        $integration = $this->integrationsManager->getByTokenForCurrentStore($paymentToken);
        $paymentMethod = $integration->getPaymentMethod();
        $paymentMethodTitle = $paymentMethod->getTitle();
        return (string)$paymentMethodTitle;
    }
}
