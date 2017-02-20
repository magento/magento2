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
        $status = static::DISABLED;

        if ($this->systemConfig->get('default/analytics/subscription/enabled')) {
            $status = static::ENABLED;

            if (!$this->analyticsToken->isTokenExist()) {
                $status = static::PENDING;
            }
        }

        return $status;
    }
}
