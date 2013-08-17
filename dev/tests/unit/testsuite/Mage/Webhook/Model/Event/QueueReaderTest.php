<?php
/**
 * Mage_Webhook_Model_Event_QueueReader
 *
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Event_QueueReaderTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webhook_Model_Event_QueueReader */
    protected $_eventQueue;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockCollection;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockIterator;

    public function setUp()
    {
        $this->_mockCollection = $this->getMockBuilder('Mage_Webhook_Model_Resource_Event_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with($this->equalTo('status'), $this->equalTo(Magento_PubSub_EventInterface::READY_TO_SEND));
        $this->_mockIterator = $this->getMockBuilder('Iterator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue($this->_mockIterator));
        $this->_eventQueue = new Mage_Webhook_Model_Event_QueueReader($this->_mockCollection);
    }

    public function testPollEvent()
    {
        $this->_mockIterator->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(true));

        $event = $this->getMockBuilder('Mage_Webhook_Model_Event')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockIterator->expects($this->once())
            ->method('current')
            ->will($this->returnValue($event));

        $event->expects($this->once())
            ->method('markAsProcessed');

        $this->_mockIterator->expects($this->once())
            ->method('next');

        $this->assertSame($event, $this->_eventQueue->poll());
    }

    public function testPollNothing()
    {
        $this->_mockIterator->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(false));

        $this->_mockIterator->expects($this->never())
            ->method('current');

        $this->_mockIterator->expects($this->never())
            ->method('next');

        $this->assertNull($this->_eventQueue->poll());
    }
}
