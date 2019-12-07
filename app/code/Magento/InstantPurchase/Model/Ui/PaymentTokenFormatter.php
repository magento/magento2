<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\Ui;

use Magento\InstantPurchase\PaymentMethodIntegration\IntegrationsManager;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Payment token string presentation.
 *
 * @api May be used for pluginization.
 */
class PaymentTokenFormatter
{
    /**
     * @var IntegrationsManager
     */
    private $integrationsManager;

    /**
     * PaymentTokenFormatter constructor.
     * @param IntegrationsManager $integrationsManager
     */
    public function __construct(IntegrationsManager $integrationsManager)
    {
        $this->integrationsManager = $integrationsManager;
    }

    /**
     * Formats payment token to string.
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    public function format(PaymentTokenInterface $paymentToken): string
    {
        $integration = $this->integrationsManager->getByTokenForCurrentStore($paymentToken);
        $formatted = $integration->formatPaymentToken($paymentToken);
        return $formatted;
    }
}
