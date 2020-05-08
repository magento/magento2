<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;

class ObserverTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    protected function setUp(): void
    {
        $this->observer = new Observer();
    }

    public function testIsValidFor()
    {
        $eventName = 'eventName';
        $eventMock = $this->createPartialMock(Event::class, ['getName']);
        $eventMock->expects($this->once())
            ->method('getName')
            ->willReturn($eventName);
        $this->observer->setData('event_name', $eventName);
        $this->assertTrue($this->observer->isValidFor($eventMock));
    }

    public function testGetName()
    {
        $name = 'some_name';
        $this->observer->setData('name', $name);
        $this->assertEquals($name, $this->observer->getName());
    }

    public function testSetName()
    {
        $name = 'some_name';
        $this->observer->setName($name);
        $result = $this->observer->getData('name');
        $this->assertEquals($result, $this->observer->getName($name));
    }

    public function testGetEventName()
    {
        $name = 'eventName';
        $this->observer->setData('event_name', $name);
        $this->assertEquals($name, $this->observer->getEventName());
    }

    public function testSetEventName()
    {
        $name = 'eventName';
        $this->observer->setEventName($name);
        $result = $this->observer->getData('event_name');
        $this->assertEquals($result, $this->observer->getEventName($name));
    }

    public function testGetCallback()
    {
        $callback = 'callbackName';
        $this->observer->setData('callback', $callback);
        $this->assertEquals($callback, $this->observer->getCallback());
    }

    public function testSetCallback()
    {
        $callback = 'callbackName';
        $this->observer->setCallback($callback);
        $result = $this->observer->getData('callback');
        $this->assertEquals($result, $this->observer->getCallback($callback));
    }

    public function testGetEvent()
    {
        $event = 'someEvent';
        $this->observer->setData('event', $event);
        $this->assertEquals($event, $this->observer->getEvent());
    }

    public function testSetEvent()
    {
        $event = 'someEvent';
        $this->observer->setEvent($event);
        $result = $this->observer->getData('event');
        $this->assertEquals($result, $this->observer->getEvent($event));
    }

    public function testDispatch()
    {
        $eventName = 'eventName';
        $callbackName = 'testCallback';
        $callbackMock = [$this->getMockBuilder(\stdClass::class)->addMethods([$callbackName])
            ->disableOriginalConstructor()
            ->getMock(), $callbackName];
        $callbackMock[0]->expects($this->once())
            ->method('testCallback')
            ->willReturn(true);
        $eventMock = $this->createPartialMock(Event::class, ['getName']);
        $eventMock->expects($this->once())
            ->method('getName')
            ->willReturn($eventName);
        $this->observer->setData('event_name', $eventName);
        $this->observer->setData('callback', $callbackMock);

        $this->observer->dispatch($eventMock);
    }

    public function testDispatchNotValidEvent()
    {
        $eventName = 'eventName';
        $notValidName = 'event_name_2';
        $eventMock = $this->createPartialMock(Event::class, ['getName']);
        $eventMock->expects($this->once())
            ->method('getName')
            ->willReturn($eventName);
        $this->observer->setData('event_name', $notValidName);

        $this->observer->dispatch($eventMock);
    }
}
