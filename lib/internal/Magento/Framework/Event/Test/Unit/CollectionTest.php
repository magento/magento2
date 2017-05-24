<?php
/**
 * @category   Magento
 * @package    Magento_Event
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Event\Test\Unit;

use \Magento\Framework\Event\Collection;

use Magento\Framework\Event;

/**
 * Class CollectionTest
 *
 * @package Magento\Framework\Event
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Collection
     */
    protected $collection;

    /*
     * Array of events in the collection
     *
     * @var array
     */
    protected $events;

    /**
     * @var \Magento\Framework\Event\Observer\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observers;

    protected function setUp()
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
        $eventMock = $this->getMock(\Magento\Framework\Event::class, ['getName'], [], '', false, false);
        $eventMock->setData('name', $eventName);
        $eventObj = $this->collection->getEventByName($eventName);
        $this->assertEquals($eventMock->getData('name'), $eventObj->getName());
    }

    public function testAddEvent()
    {
        $eventName = 'eventName';
        $eventMock = $this->getMock(\Magento\Framework\Event::class, ['getName'], [], '', false, false);
        $eventMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($eventName));
        $this->collection->addEvent($eventMock);
    }

    public function testAddObserver()
    {
        $testEvent = 'testEvent';
        $observer = new \Magento\Framework\Event\Observer();
        $observer->setData('event_name', $testEvent);

        $eventName = 'eventName';
        $eventMock = $this->getMock(\Magento\Framework\Event::class, ['getName'], [], '', false, false);
        $eventMock->setData('name', $eventName);

        $this->collection->addObserver($observer);
        $this->assertNotEmpty($this->collection->getEventByName($testEvent)->getObservers());
    }

    public function testAddObserverNoEventName()
    {
        $observer = new \Magento\Framework\Event\Observer();
        $this->collection->addObserver($observer);
        $this->assertNotEmpty($this->collection->getGlobalObservers());
    }

    public function testDispatch()
    {
        $data = ['someData'];
        $eventName = 'eventName';
        $event = new \Magento\Framework\Event($data);
        $event->setData('name', $eventName);
        $this->collection->dispatch($eventName, $data);
    }
}
