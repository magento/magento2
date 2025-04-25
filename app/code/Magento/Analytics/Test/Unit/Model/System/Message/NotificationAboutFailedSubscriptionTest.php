<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\System\Message;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Analytics\Model\System\Message\NotificationAboutFailedSubscription;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationAboutFailedSubscriptionTest extends TestCase
{
    /**
     * @var MockObject|SubscriptionStatusProvider
     */
    private $subscriptionStatusMock;

    /**
     * @var MockObject|UrlInterface
     */
    private $urlBuilderMock;

    /**
     * @var NotificationAboutFailedSubscription
     */
    private $notification;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->subscriptionStatusMock = $this->createMock(SubscriptionStatusProvider::class);
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->notification = $this->objectManagerHelper->getObject(
            NotificationAboutFailedSubscription::class,
            [
                'subscriptionStatusProvider' => $this->subscriptionStatusMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    public function testIsDisplayedWhenMessageShouldBeDisplayed()
    {
        $this->subscriptionStatusMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(
                SubscriptionStatusProvider::FAILED
            );
        $this->assertTrue($this->notification->isDisplayed());
    }

    /**
     * @dataProvider notDisplayedNotificationStatuses
     *
     * @param $status
     */
    public function testIsDisplayedWhenMessageShouldNotBeDisplayed($status)
    {
        $this->subscriptionStatusMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);
        $this->assertFalse($this->notification->isDisplayed());
    }

    public function testGetTextShouldBuildMessage()
    {
        $retryUrl = 'http://magento.dev/retryUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('analytics/subscription/retry')
            ->willReturn($retryUrl);
        $messageDetails = 'Failed to synchronize data to the Magento Business Intelligence service. ';
        $messageDetails .= sprintf('<a href="%s">Retry Synchronization</a>', $retryUrl);
        $this->assertEquals($messageDetails, $this->notification->getText());
    }

    /**
     * Provide statuses according to which message should not be displayed.
     *
     * @return array
     */
    public static function notDisplayedNotificationStatuses()
    {
        return [
            [SubscriptionStatusProvider::PENDING],
            [SubscriptionStatusProvider::DISABLED],
            [SubscriptionStatusProvider::ENABLED],
        ];
    }
}
