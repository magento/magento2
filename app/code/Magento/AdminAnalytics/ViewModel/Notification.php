<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\AdminAnalytics\Model\Condition\CanViewNotification as AdminAnalyticsNotification;
use Magento\ReleaseNotification\Model\Condition\CanViewNotification as ReleaseNotification;

/**
 * Control display of admin analytics and release notification modals
 */
class Notification implements ArgumentInterface
{
    /**
     * @var AdminAnalyticsNotification
     */
    private $canViewNotificationAnalytics;

    /**
     * @var ReleaseNotification
     */
    private $canViewNotificationRelease;

    /**
     * @param AdminAnalyticsNotification $canViewNotificationAnalytics
     * @param ReleaseNotification $canViewNotificationRelease
     */
    public function __construct(
        AdminAnalyticsNotification $canViewNotificationAnalytics,
        ReleaseNotification $canViewNotificationRelease
    ) {
        $this->canViewNotificationAnalytics = $canViewNotificationAnalytics;
        $this->canViewNotificationRelease = $canViewNotificationRelease;
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

    /**
     * Determine if the release popup is visible
     *
     * @return bool
     */
    public function isReleaseVisible(): bool
    {
        return $this->canViewNotificationRelease->isVisible([]);
    }
}
