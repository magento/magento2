<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\System\Message;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Analytics\Model\System\Message\NotificationAboutFailedSubscription;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;

/**
 * Class NotificationAboutFailedSubscriptionTest
 */
class NotificationAboutFailedSubscriptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SubscriptionStatusProvider
     */
    private $subscriptionStatusMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlInterface
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
    protected function setUp()
    {
        $this->subscriptionStatusMock = $this->getMockBuilder(SubscriptionStatusProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
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
    public function notDisplayedNotificationStatuses()
    {
        return [
            [SubscriptionStatusProvider::PENDING],
            [SubscriptionStatusProvider::DISABLED],
            [SubscriptionStatusProvider::ENABLED],
        ];
    }
}
