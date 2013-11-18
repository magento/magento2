<?php
/**
 * \Magento\Webhook\Model\Event\QueueReader
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Event;

class QueueReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webhook\Model\Event\QueueReader */
    protected $_eventQueue;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockCollection;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockIterator;

    protected function setUp()
    {
        $this->_mockCollection = $this->getMockBuilder('Magento\Webhook\Model\Resource\Event\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockIterator = $this->getMockBuilder('Iterator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue($this->_mockIterator));
        $this->_eventQueue = new \Magento\Webhook\Model\Event\QueueReader($this->_mockCollection);
    }

    public function testPollEvent()
    {
        $this->_mockIterator->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(true));

        $event = $this->getMockBuilder('Magento\Webhook\Model\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockIterator->expects($this->once())
            ->method('current')
            ->will($this->returnValue($event));

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
