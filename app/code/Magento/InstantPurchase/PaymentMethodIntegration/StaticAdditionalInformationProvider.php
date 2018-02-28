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
     */
    public function getAdditionalInformation(PaymentTokenInterface $paymentToken): array
    {
        return $this->value;
    }
}
