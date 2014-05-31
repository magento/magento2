<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Magento
 * @package    Magento_Event
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Event;

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

    public function setUp()
    {
        $this->events = [
            'eventName1' => 'someEvent1',
            'eventName2' => 'someEvent2',
            'eventName3' => 'some_event_3'
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
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getName'], [], '', false, false);
        $eventMock->setData('name', $eventName);
        $eventObj = $this->collection->getEventByName($eventName);
        $this->assertEquals($eventMock->getData('name'), $eventObj->getName());
    }

    public function testAddEvent()
    {
        $eventName = 'eventName';
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getName'], [], '', false, false);
        $eventMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($eventName));
        $this->collection->addEvent($eventMock);
    }

    public function testAddObserver()
    {
        $testEvent = 'testEvent';
        $observer = new \Magento\Framework\Event\Observer;
        $observer->setData('event_name', $testEvent);

        $eventName = 'eventName';
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getName'], [], '', false, false);
        $eventMock->setData('name', $eventName);

        $this->collection->addObserver($observer);
        $this->assertNotEmpty($this->collection->getEventByName($testEvent)->getObservers());
    }

    public function testAddObserverNoEventName()
    {
        $observer = new \Magento\Framework\Event\Observer;
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