<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Payment additional information provider that returns predefined value.
 *
 * @api
 * @since 100.2.0
 */
class StaticAdditionalInformationProvider implements PaymentAdditionalInformationProviderInterface
{
    /**
     * @var array
     */
    private $value;

    /**
     * StaticAdditionalInformationProvider constructor.
     * @param array $value
     */
    public function __construct(array $value = [])
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function getAdditionalInformation(PaymentTokenInterface $paymentToken): array
    {
        return $this->value;
    }
}
