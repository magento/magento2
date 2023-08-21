<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Provides mechanism to set specific payment additional information when payment method used with instant purchase.
 *
 * May implement any logic specific for a payment method and configured with
 * instant_purchase/additionalInformation configuration option in vault payment config.
 *
 * @api
 * @since 100.2.0
 */
interface PaymentAdditionalInformationProviderInterface
{
    /**
     * Provides additional information part specific for payment method.
     *
     * @param PaymentTokenInterface $paymentToken
     * @return array
     * @since 100.2.0
     */
    public function getAdditionalInformation(PaymentTokenInterface $paymentToken): array;
}
