<?php
/**
 * @category   Magento
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\Event;
use Magento\Framework\Event\Collection;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /*
     * Array of events in the collection
     *
     * @var array
     */
    protected $events;

    /**
     * @var \Magento\Framework\Event\Observer\Collection|MockObject
     */
    protected $observers;

    protected function setUp(): void
    {
        $this->events = [
            'eventName1' => 'someEvent1',
            'eventName2' => 'someEvent2',
            'eventName3' => 'some_event_3',
        ];
        $this->observers = new \Magento\Framework\Event\Observer\Collection();
        $this->collection = new Collection($this->events, $this->observers);
    }

    public function testGetAllEvents()
    {
        $this->assertEquals($this->events, $this->collection->getAllEvents());
    }

    public function testGetGlobalObservers()
    {
        $this->assertEquals($this->observers, $this->collection->getGlobalObservers());
    }

    public function testGetEventByName()
    {
        $eventName = 'eventName1';
        $this->assertEquals($this->events[$eventName], $this->collection->getEventByName($eventName));
    }

    public function testGetEventByNameNotSet()
    {
        $eventName = 'eventName';
        $eventMock = $this->createPartialMock(Event::class, ['getName']);
        $eventMock->setData('name', $eventName);
        $eventObj = $this->collection->getEventByName($eventName);
        $this->assertEquals($eventMock->getData('name'), $eventObj->getName());
    }

    public function testAddEvent()
    {
        $eventName = 'eventName';
        $eventMock = $this->createPartialMock(Event::class, ['getName']);
        $eventMock->expects($this->once())
            ->method('getName')
            ->willReturn($eventName);
        $this->collection->addEvent($eventMock);
    }

    public function testAddObserver()
    {
        $testEvent = 'testEvent';
        $observer = new Observer();
        $observer->setData('event_name', $testEvent);

        $eventName = 'eventName';
        $eventMock = $this->createPartialMock(Event::class, ['getName']);
        $eventMock->setData('name', $eventName);

        $this->collection->addObserver($observer);
        $this->assertNotEmpty($this->collection->getEventByName($testEvent)->getObservers());
    }

    public function testAddObserverNoEventName()
    {
        $observer = new Observer();
        $this->collection->addObserver($observer);
        $this->assertNotEmpty($this->collection->getGlobalObservers());
    }

    public function testDispatch()
    {
        $data = ['someData'];
        $eventName = 'eventName';
        $event = new Event($data);
        $event->setData('name', $eventName);
        $this->collection->dispatch($eventName, $data);
    }
}
