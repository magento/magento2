<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
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
        $status = "Disabled";

        if ($this->systemConfig->get('default/analytics/subscription/enabled')) {
            $status = "Enabled";

            if (!$this->analyticsToken->isTokenExist()) {
                $status = "Pending";
            }
        }

        return $status;
    }
}
