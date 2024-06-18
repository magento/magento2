<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\EventManager.
 */
namespace Magento\Test;

class EventManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\EventManager
     */
    protected $_eventManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_subscriberOne;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_subscriberTwo;

    protected function setUp(): void
    {
        $this->_subscriberOne = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['testEvent'])
            ->getMock();
        $this->_subscriberTwo = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['testEvent'])
            ->getMock();
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
        $this->_subscriberOne->expects($this->once())->method('testEvent')->willReturnCallback($callback);
        $callback = function () use (&$actualSubscribers) {
            $actualSubscribers[] = 'subscriberTwo';
        };
        $this->_subscriberTwo->expects($this->once())->method('testEvent')->willReturnCallback($callback);
        $this->_eventManager->fireEvent('testEvent', [], $reverseOrder);
        $this->assertEquals($expectedSubscribers, $actualSubscribers);
    }

    public static function fireEventDataProvider()
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
