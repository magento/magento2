<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\PaymentMethodIntegration;

/**
 * Availability checker with predefined result.
 *
 * @api
 * @since 100.2.0
 */
class StaticAvailabilityChecker implements AvailabilityCheckerInterface
{
    /**
     * @var bool
     */
    private $value;

    /**
     * AlwaysAvailable constructor.
     * @param bool $value
     */
    public function __construct(bool $value = true)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function isAvailable(): bool
    {
        return $this->value;
    }
}
