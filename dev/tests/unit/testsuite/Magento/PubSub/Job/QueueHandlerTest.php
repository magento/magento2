<?php
/**
 * \Magento\PubSub\Job\QueueHandler
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
namespace Magento\PubSub\Job;

class QueueHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\PubSub\Job\QueueHandler */
    private $_queueHandler;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_subscriptionMockA;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_subscriptionMockB;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_eventMockA;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_eventMockB;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_queueReaderMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_queueWriterMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_messageMockA;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_messageMockB;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_msgFactoryMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_transportMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_endpointMockA;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_endpointMockB;

    protected function setUp()
    {
        // Object mocks
        $this->_subscriptionMockA = $this->_makeMock('Magento\Webhook\Model\Subscription');
        $this->_subscriptionMockB =  $this->_makeMock('Magento\Webhook\Model\Subscription');
        $this->_eventMockA = $this->_makeMock('Magento\Webhook\Model\Event');
        $this->_eventMockB = $this->_makeMock('Magento\Webhook\Model\Event');
        $this->_msgFactoryMock = $this->_makeMock('Magento\Outbound\Message\Factory');
        $this->_transportMock = $this->_makeMock('Magento\Outbound\Transport\Http');
        $this->_queueReaderMock = $this->_makeMock('Magento\Webhook\Model\Job\QueueReader');
        $this->_queueWriterMock = $this->_makeMock('Magento\Webhook\Model\Job\QueueWriter');
        $this->_messageMockA = $this->_makeMock('Magento\Outbound\Message');
        $this->_messageMockB = $this->_makeMock('Magento\Outbound\Message');
        $this->_endpointMockA = $this->_makeMock('Magento\Outbound\EndpointInterface');
        $this->_endpointMockB = $this->_makeMock('Magento\Outbound\EndpointInterface');

        $this->_subscriptionMockA->expects($this->any())
            ->method('getEndpoint')
            ->will($this->returnValue($this->_endpointMockA));

        $this->_subscriptionMockB->expects($this->any())
            ->method('getEndpoint')
            ->will($this->returnValue($this->_endpointMockB));

        $this->_eventMockA->expects($this->any())
            ->method('getTopic')
            ->will($this->returnValue('topicA'));

        $this->_eventMockA->expects($this->any())
            ->method('getBodyData')
            ->will($this->returnValue(array('BodyDataA')));

        $this->_eventMockB->expects($this->any())
            ->method('getTopic')
            ->will($this->returnValue('topicB'));

        $this->_eventMockB->expects($this->any())
            ->method('getBodyData')
            ->will($this->returnValue(array('BodyDataB')));
    }

    public function testHandle()
    {
        // Resources for stubs
        $jobMsgMap = array(
            array($this->_endpointMockA, 'topicA', array('BodyDataA'), $this->_messageMockA),
            array($this->_endpointMockB, 'topicB', array('BodyDataB'), $this->_messageMockB),
        );

        $responseA = $this->_makeMock('Magento\Outbound\Transport\Http\Response');
        $responseB = $this->_makeMock('Magento\Outbound\Transport\Http\Response');

        $responseA->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(true));

        $responseB->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(false));

        $msgResponseMap = array(
            array($this->_messageMockA, $responseA),
            array($this->_messageMockB, $responseB),
        );

        // Message factory create
        $this->_msgFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap($jobMsgMap));

        // Transport dispatch
        $this->_transportMock->expects($this->exactly(2))
            ->method('dispatch')
            ->will($this->returnValueMap($msgResponseMap));

        // Job stubs
        $jobMockA = $this->_makeMock('Magento\Webhook\Model\Job');
        $jobMockB = $this->_makeMock('Magento\Webhook\Model\Job');

        $jobMockA->expects($this->once())
            ->method('complete');

        $jobMockB->expects($this->once())
            ->method('handleFailure');

        $jobMockA->expects($this->once())
            ->method('getSubscription')
            ->with()
            ->will($this->returnValue($this->_subscriptionMockA));

        $jobMockB->expects($this->once())
            ->method('getSubscription')
            ->with()
            ->will($this->returnValue($this->_subscriptionMockB));

        $jobMockA->expects($this->once())
            ->method('getEvent')
            ->with()
            ->will($this->returnValue($this->_eventMockA));

        $jobMockB->expects($this->once())
            ->method('getEvent')
            ->with()
            ->will($this->returnValue($this->_eventMockB));

        // Queue contains two jobs, and will then return null to stop the loop
        $this->_queueReaderMock->expects($this->exactly(3))
            ->method('poll')
            ->with()
            ->will($this->onConsecutiveCalls(
                $jobMockA,
                $jobMockB,
                null
            ));

        $this->_queueHandler = new \Magento\PubSub\Job\QueueHandler(
            $this->_queueReaderMock,
            $this->_queueWriterMock,
            $this->_transportMock,
            $this->_msgFactoryMock
        );

        $this->_queueHandler->handle();
    }

    public function testHandleEmptyQueue()
    {
        $this->_expectedCodes = array ();

        // Queue contains no jobs
        $this->_queueReaderMock->expects($this->once())
            ->method('poll')
            ->with()
            ->will($this->onConsecutiveCalls(
                null
            ));

        // Message factory create should never  be called
        $this->_msgFactoryMock->expects($this->never())
            ->method('create');

        // Transport dispatch should never be called
        $this->_transportMock->expects($this->never())
            ->method('dispatch');

        $this->_queueHandler = new \Magento\PubSub\Job\QueueHandler(
            $this->_queueReaderMock,
            $this->_queueWriterMock,
            $this->_transportMock,
            $this->_msgFactoryMock
        );

        $this->_queueHandler->handle();
    }

    /**
     * Generates a mock object of the given class
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _makeMock($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
