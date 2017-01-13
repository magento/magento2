<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\EventManager.
 */
namespace Magento\Test;

class EventManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\EventManager
     */
    protected $_eventManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subscriberOne;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subscriberTwo;

    protected function setUp()
    {
        $this->_subscriberOne = $this->getMock(\stdClass::class, ['testEvent']);
        $this->_subscriberTwo = $this->getMock(\stdClass::class, ['testEvent']);
        $this->_eventManager = new \Magento\TestFramework\EventManager(
            [$this->_subscriberOne, $this->_subscriberTwo]
        );
    }

    /**
     * @param bool $reverseOrder
     * @param array $expectedSubscribers
     * @dataProvider fireEventDataProvider
     */
    public function testFireEvent($reverseOrder, $expectedSubscribers)
    {
        $actualSubscribers = [];
        $callback = function () use (&$actualSubscribers) {
            $actualSubscribers[] = 'subscriberOne';
        };
        $this->_subscriberOne->expects($this->once())->method('testEvent')->will($this->returnCallback($callback));
        $callback = function () use (&$actualSubscribers) {
            $actualSubscribers[] = 'subscriberTwo';
        };
        $this->_subscriberTwo->expects($this->once())->method('testEvent')->will($this->returnCallback($callback));
        $this->_eventManager->fireEvent('testEvent', [], $reverseOrder);
        $this->assertEquals($expectedSubscribers, $actualSubscribers);
    }

    public function fireEventDataProvider()
    {
        return [
            'straight order' => [false, ['subscriberOne', 'subscriberTwo']],
            'reverse order' => [true, ['subscriberTwo', 'subscriberOne']]
        ];
    }

    public function testFireEventParameters()
    {
        $paramOne = 123;
        $paramTwo = 456;
        $this->_subscriberOne->expects($this->once())->method('testEvent')->with($paramOne, $paramTwo);
        $this->_subscriberTwo->expects($this->once())->method('testEvent')->with($paramOne, $paramTwo);
        $this->_eventManager->fireEvent('testEvent', [$paramOne, $paramTwo]);
    }
}
