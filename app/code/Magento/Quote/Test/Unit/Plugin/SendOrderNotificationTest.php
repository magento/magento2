<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Plugin;

use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Observer\SubmitObserver;
use Magento\Quote\Plugin\SendOrderNotification;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order;
use Magento\Framework\Event;

/**
 * Unit test for SendOrderNotification plugin
 */
class SendOrderNotificationTest extends TestCase
{
    /**
     * @var RequestInterface|RequestInterface&MockObject|MockObject
     */
    private RequestInterface $request;

    /**
     * @var SubmitObserver|SubmitObserver&MockObject|MockObject
     */
    private SubmitObserver $subject;

    /**
     * @var Observer|Observer&MockObject|MockObject
     */
    private Observer $observer;

    /**
     * @var SendOrderNotification
     */
    private SendOrderNotification $notification;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->subject = $this->createMock(SubmitObserver::class);
        $this->observer = $this->createMock(Observer::class);
        $this->notification = new SendOrderNotification($this->request);
    }

    /**
     * @return void
     */
    public function testBeforeExecuteWithSendConfirmation()
    {
        $this->request->expects($this->once())->method('getParam')->with('order')
            ->willReturn(['send_confirmation' => 1]);

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOrder'])
            ->getMock();
        $event->expects($this->exactly(2))->method('getOrder')->willReturn($order);

        $this->observer->expects($this->exactly(2))->method('getEvent')->willReturn($event);

        $result = $this->notification->beforeExecute($this->subject, $this->observer);
        $this->assertIsArray($result);
        $this->assertContains($this->observer, $result);

        $observerCheck = $result[0];
        /** @var  Order $orderCheck */
        $orderCheck = $observerCheck->getEvent()->getOrder();
        $this->assertTrue($orderCheck->getCanSendNewEmailFlag());
    }

    /**
     * @return void
     */
    public function testBeforeExecuteWithoutSendConfirmation()
    {
        $this->request->expects($this->once())->method('getParam')->with('order')
            ->willReturn(['order' => []]);

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOrder'])
            ->getMock();
        $event->expects($this->exactly(2))->method('getOrder')->willReturn($order);

        $this->observer->expects($this->exactly(2))->method('getEvent')->willReturn($event);

        $result = $this->notification->beforeExecute($this->subject, $this->observer);
        $this->assertIsArray($result);
        $this->assertContains($this->observer, $result);

        $observerCheck = $result[0];
        /** @var  Order $orderCheck */
        $orderCheck = $observerCheck->getEvent()->getOrder();
        $this->assertFalse($orderCheck->getCanSendNewEmailFlag());
    }
}
