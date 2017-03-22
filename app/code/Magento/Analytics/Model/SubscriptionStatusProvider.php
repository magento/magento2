<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Config\App\Config\Type\System;

/**
 * Provider of subscription status.
 */
class SubscriptionStatusProvider
{
    /**
     * Represents an enabled subscription state.
     */
    const ENABLED = "Enabled";

    /**
     * Represents a pending subscription state.
     */
    const PENDING = "Pending";

    /**
     * Represents a disabled subscription state.
     */
    const DISABLED = "Disabled";

    /**
     * @var System
     */
    private $systemConfig;

    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @param System $systemConfig
     * @param AnalyticsToken $analyticsToken
     */
    public function __construct(
        System $systemConfig,
        AnalyticsToken $analyticsToken
    ) {
        $this->systemConfig = $systemConfig;
        $this->analyticsToken = $analyticsToken;
    }

    /**
     * Statuses:
     *
     * Enabled - if subscription is enabled and MA token was received;
     * Pending - if subscription is enabled and MA token was not received;
     * Disabled - if subscription is not enabled.
     *
     * @return string
     */
    public function getStatus()
    {
        $checkboxState = $this->systemConfig->get('default/analytics/subscription/enabled');
        return $this->resolveStatus($checkboxState);
    }

    /**
     * Resolves subscription status depending on
     * subscription config value (enabled, disabled).
     *
     * @param bool $isSubscriptionEnabled
     *
     * @return string
     */
    private function resolveStatus($isSubscriptionEnabled)
    {
        if (!$isSubscriptionEnabled) {
            return static::DISABLED;
        }

        $status = static::PENDING;

        if ($this->analyticsToken->isTokenExist()) {
            $status = static::ENABLED;
        }

        return $status;
    }

    /**
     * Retrieve status for subscription that enabled in config.
     *
     * @return string
     */
    public function getStatusForEnabledSubscription()
    {
        return $this->resolveStatus(true);
    }

    /**
     * Retrieve status for subscription that disabled in config.
     *
     * @return string
     */
    public function getStatusForDisabledSubscription()
    {
        return $this->resolveStatus(false);
    }
}
