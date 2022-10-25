<?php

namespace Magento\Quote\Test\Unit\Plugin;

use Magento\Framework\Event\Observer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Observer\SubmitObserver;
use Magento\Quote\Plugin\SendOrderNotification;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order;
use Magento\Framework\Event;

class SendOrderNotificationTest extends TestCase
{
    private RestRequest $request;
    private SubmitObserver $subject;
    private Observer $observer;
    private SendOrderNotification $notification;

    protected function setUp(): void
    {
        $this->request = $this->createMock(RestRequest::class);
        $this->subject = $this->createMock(SubmitObserver::class);
        $this->observer = $this->createMock(Observer::class);
        $this->notification = new SendOrderNotification($this->request);
    }

    public function testBeforeExecuteWithSendConfirmation()
    {
        $this->request->expects($this->once())->method('getPostValue')->with('order')
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

    public function testBeforeExecuteWithoutSendConfirmation()
    {
        $this->request->expects($this->once())->method('getPostValue')->with('order')
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
