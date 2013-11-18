<?php
/**
 * \Magento\PubSub\Message\DispatcherAsync
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PubSub\Message;

class DispatcherAsyncTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\PubSub\Message\DispatcherAsync */
    private $_dispatcher;

    /** \PHPUnit_Framework_MockObject_MockObject */
    private $_eventFactoryMock;

    /** \PHPUnit_Framework_MockObject_MockObject */
    private $_eventMock;

    /** @var  string[] Data that gets passed to event factory */
    private $_actualData = array();

    /** \PHPUnit_Framework_MockObject_MockObject */
    private $_queueWriter;


    protected function setUp()
    {
        $this->_eventFactoryMock = $this->getMockBuilder('Magento\PubSub\Event\FactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_queueWriter = $this->getMockBuilder('Magento\PubSub\Event\QueueWriterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_queueWriter->expects($this->once())
            ->method('offer');

        // When the create method is called, program routes to the logEventData callback to log what arguments it
        // received.
        $this->_eventFactoryMock->expects($this->once())
            ->method('create')
            ->with()
            ->will($this->returnCallback(array($this, 'logEventData')));
        $this->_eventMock = $this->getMockBuilder('Magento\PubSub\EventInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_dispatcher = new \Magento\PubSub\Message\DispatcherAsync($this->_eventFactoryMock, $this->_queueWriter);
    }

    public function testDispatch()
    {
        $expectedData = array('topic' => 'event_topic', 'data' => 'event_data');
        $this->_dispatcher->dispatch($expectedData['topic'], $expectedData['data']);
        $this->assertEquals($expectedData, $this->_actualData);
    }

    /**
     * Log the topic and data that are passed to the event factory's create method. This is to ensure that the
     * create method is called as expected.
     *
     * @param $topic
     * @param $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function logEventData($topic, $data)
    {
        $this->_actualData = array('topic' => $topic, 'data' => $data);
        return $this->_eventMock;
    }
}
