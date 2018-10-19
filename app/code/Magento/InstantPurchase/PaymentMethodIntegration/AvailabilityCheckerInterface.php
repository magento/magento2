<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

/**
 * Provides mechanism to configure availability of vault integration with instant purchase.
 *
 * May implement any logic specific for a payment method and configured with
 * instant_purchase/available configuration option in vault payment config.
 *
 * @api
 */
interface AvailabilityCheckerInterface
{
    /**
     * Checks if payment method may be used for instant purchase.
     *
     * @return bool
     */
    public function isAvailable(): bool;
}
