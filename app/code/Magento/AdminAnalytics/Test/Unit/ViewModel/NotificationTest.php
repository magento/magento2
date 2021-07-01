<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Test\Unit\ViewModel;

use Magento\AdminAnalytics\Model\Condition\CanViewNotification as AdminAnalyticsNotification;
use Magento\AdminAnalytics\ViewModel\Notification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ReleaseNotification\Model\Condition\CanViewNotification as ReleaseNotification;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var AdminAnalyticsNotification|MockObject
     */
    private $canViewNotificationAnalytics;

    /**
     * @var ReleaseNotification|MockObject
     */
    private $canViewNotificationRelease;

    protected function setUp(): void
    {
        $this->canViewNotificationAnalytics = $this->createMock(AdminAnalyticsNotification::class);
        $this->canViewNotificationRelease = $this->createMock(ReleaseNotification::class);

        $objectManager = new ObjectManager($this);
        $this->notification = $objectManager->getObject(
            Notification::class,
            [
                'canViewNotificationAnalytics' => $this->canViewNotificationAnalytics,
                'canViewNotificationRelease' => $this->canViewNotificationRelease
            ]
        );
    }

    public function testIsAnalyticsVisible()
    {
        $this->canViewNotificationAnalytics
            ->expects($this->once())
            ->method('isVisible')
            ->with([])
            ->willReturn(true);

        $this->assertSame(true, $this->notification->isAnalyticsVisible());
    }

    public function testIsReleaseVisible()
    {
        $this->canViewNotificationRelease
            ->expects($this->once())
            ->method('isVisible')
            ->with([])
            ->willReturn(false);

        $this->assertSame(false, $this->notification->isReleaseVisible());
    }
}
