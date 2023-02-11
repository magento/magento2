<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\AdminAnalytics\Model\Condition\CanViewNotification as AdminAnalyticsNotification;

/**
 * Control display of admin analytics
 */
class Notification implements ArgumentInterface
{
    /**
     * @var AdminAnalyticsNotification
     */
    private $canViewNotificationAnalytics;

    /**
     * @param AdminAnalyticsNotification $canViewNotificationAnalytics
     */
    public function __construct(
        AdminAnalyticsNotification $canViewNotificationAnalytics
    ) {
        $this->canViewNotificationAnalytics = $canViewNotificationAnalytics;
    }

    /**
     * Determine if the analytics popup is visible
     *
     * @return bool
     */
    public function isAnalyticsVisible(): bool
    {
        return $this->canViewNotificationAnalytics->isVisible([]);
    }
}
