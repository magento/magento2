<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminAnalytics\ViewModel;

/**
 * Class Notification
 */
class Notification implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\AdminAnalytics\Model\Condition\CanViewNotification
     */
    private $canViewNotificationAnalytics;

    /**
     * @var \Magento\ReleaseNotification\Model\Condition\CanViewNotification
     */
    private $canViewNotificationRelease;

    /**
     * Notification constructor.
     * @param \Magento\AdminAnalytics\Model\Condition\CanViewNotification $canViewNotificationAnalytics
     * @param \Magento\ReleaseNotification\Model\Condition\CanViewNotification $canViewNotificationRelease
     */
    public function __construct(
        \Magento\AdminAnalytics\Model\Condition\CanViewNotification $canViewNotificationAnalytics,
        \Magento\ReleaseNotification\Model\Condition\CanViewNotification $canViewNotificationRelease
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
