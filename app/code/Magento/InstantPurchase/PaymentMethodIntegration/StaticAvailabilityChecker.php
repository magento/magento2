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
     */
    public function isAvailable(): bool
    {
        return $this->value;
    }
}
