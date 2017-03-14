<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\System\Message;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Analytics\Model\System\Message\Notification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;

/**
 * Class NotificationTest
 */
class NotificationTest extends \PHPUnit_Framework_TestCase
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
     * @var Notification
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
        $this->subscriptionStatusMock = $this->getMockBuilder(
            SubscriptionStatusProvider::class
        )->disableOriginalConstructor()->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->notification = $this->objectManagerHelper->getObject(
            Notification::class,
            [
                'subscriptionStatusProvider' => $this->subscriptionStatusMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    public function testMessageShouldBeDisplayed()
    {
        $this->subscriptionStatusMock->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(
                \Magento\Analytics\Model\SubscriptionStatusProvider::FAILED,
                \Magento\Analytics\Model\SubscriptionStatusProvider::ENABLED
            );
        $this->assertTrue($this->notification->isDisplayed());
        $this->assertFalse($this->notification->isDisplayed());
    }

    public function testBuildMessage()
    {
        $retryUrl = 'http://magento.dev/retryUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($retryUrl);
        $messageDetails = 'Failed to synchronize data to the Magento Business Intelligence service. ';
        $messageDetails .= sprintf('<a href="%s">Retry Synchronization</a>', $retryUrl);
        $this->assertEquals($messageDetails, $this->notification->getText());
    }
}
