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
        $this->_subscriberOne = $this->getMock('stdClass', array('testEvent'));
        $this->_subscriberTwo = $this->getMock('stdClass', array('testEvent'));
        $this->_eventManager = new \Magento\TestFramework\EventManager(
            array($this->_subscriberOne, $this->_subscriberTwo)
        );
    }

    /**
     * @param bool $reverseOrder
     * @param array $expectedSubscribers
     * @dataProvider fireEventDataProvider
     */
    public function testFireEvent($reverseOrder, $expectedSubscribers)
    {
        $actualSubscribers = array();
        $callback = function () use (&$actualSubscribers) {
            $actualSubscribers[] = 'subscriberOne';
        };
        $this->_subscriberOne->expects($this->once())->method('testEvent')->will($this->returnCallback($callback));
        $callback = function () use (&$actualSubscribers) {
            $actualSubscribers[] = 'subscriberTwo';
        };
        $this->_subscriberTwo->expects($this->once())->method('testEvent')->will($this->returnCallback($callback));
        $this->_eventManager->fireEvent('testEvent', array(), $reverseOrder);
        $this->assertEquals($expectedSubscribers, $actualSubscribers);
    }

    public function fireEventDataProvider()
    {
        return array(
            'straight order' => array(false, array('subscriberOne', 'subscriberTwo')),
            'reverse order' => array(true, array('subscriberTwo', 'subscriberOne'))
        );
    }

    public function testFireEventParameters()
    {
        $paramOne = 123;
        $paramTwo = 456;
        $this->_subscriberOne->expects($this->once())->method('testEvent')->with($paramOne, $paramTwo);
        $this->_subscriberTwo->expects($this->once())->method('testEvent')->with($paramOne, $paramTwo);
        $this->_eventManager->fireEvent('testEvent', array($paramOne, $paramTwo));
    }
}
