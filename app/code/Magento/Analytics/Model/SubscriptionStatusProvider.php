<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;

/**
 * Provider of subscription status.
 * @since 2.2.0
 */
class SubscriptionStatusProvider
{
    /**
     * Represents an enabled subscription state.
     */
    const ENABLED = "Enabled";

    /**
     * Represents a failed subscription state.
     */
    const FAILED = "Failed";

    /**
     * Represents a pending subscription state.
     */
    const PENDING = "Pending";

    /**
     * Represents a disabled subscription state.
     */
    const DISABLED = "Disabled";

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param AnalyticsToken $analyticsToken
     * @param FlagManager $flagManager
     * @since 2.2.0
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        AnalyticsToken $analyticsToken,
        FlagManager $flagManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->analyticsToken = $analyticsToken;
        $this->flagManager = $flagManager;
    }

    /**
     * Retrieve subscription status to Magento BI Advanced Reporting.
     *
     * Statuses:
     * Enabled - if subscription is enabled and MA token was received;
     * Pending - if subscription is enabled and MA token was not received;
     * Disabled - if subscription is not enabled.
     * Failed - if subscription is enabled and token was not received after attempts ended.
     *
     * @return string
     * @since 2.2.0
     */
    public function getStatus()
    {
        $isSubscriptionEnabledInConfig = $this->scopeConfig->getValue('analytics/subscription/enabled');
        if ($isSubscriptionEnabledInConfig) {
            return $this->getStatusForEnabledSubscription();
        }

        return $this->getStatusForDisabledSubscription();
    }

    /**
     * Retrieve status for subscription that enabled in config.
     *
     * @return string
     * @since 2.2.0
     */
    public function getStatusForEnabledSubscription()
    {
        $status = static::ENABLED;
        if ($this->flagManager->getFlagData(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE)) {
            $status = self::PENDING;
        }

        if (!$this->analyticsToken->isTokenExist()) {
            $status = static::PENDING;
            if ($this->flagManager->getFlagData(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE) === null) {
                $status = static::FAILED;
            }
        }

        return $status;
    }

    /**
     * Retrieve status for subscription that disabled in config.
     *
     * @return string
     * @since 2.2.0
     */
    public function getStatusForDisabledSubscription()
    {
        return static::DISABLED;
    }
}
