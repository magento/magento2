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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Event;

use Magento\Framework\Event;

/**
 * Class ConfigTest
 *
 * @package Magento\Framework\Event
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    public function setUp()
    {
        $this->observer = new Observer();
    }

    public function testIsValidFor()
    {
        $eventName = 'eventName';
        $eventMock = $this->getMock('Magento\Framework\Event', ['getName']);
        $eventMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($eventName));
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
        $callbackMock = [$this->getMock('stdClass', [$callbackName]), $callbackName];
        $callbackMock[0]->expects($this->once())
            ->method('testCallback')
            ->will($this->returnValue(true));
        $eventMock = $this->getMock('Magento\Framework\Event', ['getName']);
        $eventMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($eventName));
        $this->observer->setData('event_name', $eventName);
        $this->observer->setData('callback', $callbackMock);

        $this->observer->dispatch($eventMock);
    }

    public function testDispatchNotValidEvent()
    {
        $eventName = 'eventName';
        $notValidName = 'event_name_2';
        $eventMock = $this->getMock('Magento\Framework\Event', ['getName']);
        $eventMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($eventName));
        $this->observer->setData('event_name', $notValidName);

        $this->observer->dispatch($eventMock);
    }
}
