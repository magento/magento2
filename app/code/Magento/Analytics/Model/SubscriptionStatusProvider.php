<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Config\App\Config\Type\System;

/**
 * Class SubscriptionStatusProvider
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
     * SubscriptionStatusProvider constructor.
     *
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
     * "Enabled" status if Dropdown is Yes and if MA token was received
     * "Pending" status if Dropdown is Yes and if MA token was not received
     * "Disabled" status if Dropdown is No
     *
     * @return string
     */
    public function getStatus()
    {
        $status = "Disabled";
        $isSubscriptionEnabled = $this->isSubscriptionEnabled();
        $hasToken = $this->analyticsToken->isTokenExist();
        if ($isSubscriptionEnabled && $hasToken) {
            $status = "Enabled";
        } elseif ($isSubscriptionEnabled && !$hasToken) {
            $status = "Pending";
        }

        return $status;
    }

    /**
     * @return bool
     */
    private function isSubscriptionEnabled()
    {
        return (bool)$this->systemConfig->get('default/analytics/subscription/enabled');
    }
}
