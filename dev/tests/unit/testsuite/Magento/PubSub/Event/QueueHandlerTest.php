<?php
/**
 * \Magento\PubSub\Event\QueueHandler
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
namespace Magento\PubSub\Event;

class QueueHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TOPIC = 'some_topic';
    const ANOTHER_TOPIC = 'some_other_topic';

    /** @var  \Magento\PubSub\Event\QueueHandler */
    private $_queueHandler;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_subscriptionMockA;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_subscriptionMockB;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_eventQueueMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_jobQueueMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_jobFactoryMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_subxCollectionMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_eventMockA;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_eventMockB;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $_jobMock;

    /** @var  array Topics mapped to arrays of subscriptions */
    private $_actualJobsMap;


    protected function setUp()
    {
        /**
         * Mock objects
         */
        $this->_subscriptionMockA = $this->getMockBuilder('Magento\Webhook\Model\Subscription')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_subscriptionMockB = clone $this->_subscriptionMockA;

        $this->_eventQueueMock = $this->getMockBuilder('Magento\Webhook\Model\Event\QueueReader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_jobQueueMock = $this->getMockBuilder('Magento\Webhook\Model\Job\QueueWriter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_jobFactoryMock = $this->getMockBuilder('Magento\Webhook\Model\Job\Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_subxCollectionMock = $this->getMockBuilder('Magento\Webhook\Model\Resource\Subscription\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_eventMockA = $this->getMockBuilder('Magento\Webhook\Model\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_eventMockB = clone $this->_eventMockA;

        $this->_jobMock = $this->getMockBuilder('Magento\Webhook\Model\Job')
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * Method stubs
         */
        $this->_eventQueueMock->expects($this->exactly(3))
            ->method('poll')
            ->with()
            ->will($this->onConsecutiveCalls(
                $this->_eventMockA,
                $this->_eventMockB,
                null
            ));

        $this->_eventMockA->expects($this->exactly(2)) //used in handle() and logJob()
            ->method('getTopic')
            ->with()
            ->will($this->returnValue(self::TOPIC));

        $this->_eventMockB->expects($this->exactly(2)) //used in handle() and logJob()
            ->method('getTopic')
            ->with()
            ->will($this->returnValue(self::ANOTHER_TOPIC));

        $subxByTopic = array(
            array(self::TOPIC, array($this->_subscriptionMockA)),
            array(self::ANOTHER_TOPIC, array($this->_subscriptionMockB)),
        );

        $this->_subxCollectionMock->expects($this->exactly(2))
            ->method('getSubscriptionsByTopic')
            ->will($this->returnValueMap( $subxByTopic ));

        $callback = array($this, 'logJob');

        $this->_jobFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnCallback($callback));
    }

    public function testHandle()
    {
        $this->_queueHandler = new \Magento\PubSub\Event\QueueHandler(
            $this->_eventQueueMock,
            $this->_jobQueueMock,
            $this->_jobFactoryMock,
            $this->_subxCollectionMock
        );

        /**
         * Expected map of event topics to subscriptions. Values are arrays because one topic can map to multiple
         * subscriptions.
         */
        $expectedJobsMap = array(
            self::TOPIC => array($this->_subscriptionMockA),
            self::ANOTHER_TOPIC => array($this->_subscriptionMockB),
        );

        $this->_queueHandler->handle();

        /**
         * Verifies that QueueHandler effectively polls the queue, gets the topic, and creates jobs for every \
         * subscription associated with every event.
         */
        $this->assertEquals($expectedJobsMap, $this->_actualJobsMap);
    }

    /**
     * Logs when a the job factory calls the create method stub
     *
     * @param $subscription
     * @param $event
     * @return \PHPUnit_Framework_MockObject_MockObject  Is a mock of \Magento\Webhook\Model\Job
     */
    public function logJob($subscription, $event)
    {
        $this->_actualJobsMap[$event->getTopic()][] = $subscription;
        return $this->_jobMock;
    }
}
