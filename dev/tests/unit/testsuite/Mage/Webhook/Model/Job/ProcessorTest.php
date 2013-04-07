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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Job_ProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Job_Processor
     */
    protected $_mockObject;

    public function setUp()
    {
        parent::setUp();

        $this->_mockObject = $this->getMock('Mage_Webhook_Model_Job_Processor',
                                            array('getResourceSubscriberCollection', '_newDispatchJob'));
    }

    protected function _createEventQueue()
    {
        return $this->getMockBuilder('Mage_Webhook_Model_Event_Queue')
                ->disableOriginalConstructor()
                ->setMethods(array('poll'))
                ->getMock();
    }

    protected function _createEventForQueue()
    {
        return $this->getMockBuilder('Mage_Webhook_Model_Event')
                ->disableOriginalConstructor()
                ->getMock();
    }

    public function testCreateJobsFromQueueNone()
    {
        $eventQueue = $this->_createEventQueue();
        $eventQueue->expects($this->once())->method('poll')->will($this->returnValue(null));
        $this->_mockObject->createJobsFromQueue($eventQueue);
    }

    public function testCreateJobsFromQueueOne()
    {
        $this->_mockObject = $this->getMock('Mage_Webhook_Model_Job_Processor',
                                            array('createJobs'));
        $eventQueue        = $this->_createEventQueue();

        $event1 = $this->_createEventForQueue();

        $eventQueue->expects($this->exactly(2))->method('poll')->will($this->onConsecutiveCalls($event1, null));
        $this->_mockObject->expects($this->once())->method('createJobs')->with($event1);
        $this->_mockObject->createJobsFromQueue($eventQueue);
    }

    public function testCreateJobsFromQueueMany()
    {
        $this->_mockObject = $this->getMock('Mage_Webhook_Model_Job_Processor',
                                            array('createJobs'));
        $eventQueue        = $this->_createEventQueue();

        $event1 = $this->_createEventForQueue();
        $event2 = $this->_createEventForQueue();

        $eventQueue->expects($this->exactly(3))->method('poll')->will($this->onConsecutiveCalls($event1, $event2,
                                                                                                null));
        $this->_mockObject->expects($this->at(0))->method('createJobs')->with($event1);
        $this->_mockObject->expects($this->at(1))->method('createJobs')->with($event1);
        $this->_mockObject->createJobsFromQueue($eventQueue);
    }

    protected function _createEventForJob($topic, $format, $eventId, $numberOfIdCalls)
    {
        $event = $this->getMockBuilder('Mage_Webhook_Model_Event')
                      ->disableOriginalConstructor()
                      ->setMethods(array('getTopic', 'getMapping', 'getId'))
                      ->getMock();

        $event->expects($this->once())
                ->method('getTopic')
                ->will($this->returnValue($topic));
        $event->expects($this->once())
              ->method('getMapping')
              ->will($this->returnValue($format));

        if (!is_null($eventId)) {
            $event->expects($this->exactly($numberOfIdCalls))
                    ->method('getId')
                    ->will($this->returnValue($eventId));
        }

        return $event;
    }

    protected function _createSubscriberForEvent($subId)
    {
        $sub = $this->getMockBuilder('Mage_Webhook_Model_Subscriber')
                ->disableOriginalConstructor()
                ->setMethods(array('getId'))
                ->getMock();
        $sub->expects($this->once())->method('getId')->will($this->returnValue($subId));

        return $sub;
    }

    protected function _createSubscriberCollection(array $subscribers, $topic, $mapping)
    {
        $subscriberCollection = $this->getMockBuilder('Mage_Webhook_Model_Resource_Subscriber_Collection')
                ->disableOriginalConstructor()
                ->setMethods(array('getIterator', 'addTopicFilter', 'addMappingFilter', 'addIsActiveFilter'))
                ->getMock();
        if (!is_null($subscribers)) {
            $subscriberCollection->expects($this->once())
                    ->method('getIterator')
                    ->will($this->returnValue(new ArrayIterator($subscribers)));
            $subscriberCollection->expects($this->once())
                    ->method('addTopicFilter')
                    ->with($topic)
                    ->will($this->returnSelf());
            $subscriberCollection->expects($this->once())
                    ->method('addMappingFilter')
                    ->with($mapping)
                    ->will($this->returnSelf());
            $subscriberCollection->expects($this->once())
                    ->method('addIsActiveFilter')
                    ->with(Mage_Webhook_Model_Subscriber::STATUS_ACTIVE)
                    ->will($this->returnSelf());
        }

        $this->_mockObject->expects($this->once())
                ->method('getResourceSubscriberCollection')
                ->will($this->returnValue($subscriberCollection));

        return $subscriberCollection;
    }

    protected function _createExpectedJob($subId, $eventId)
    {
        $job = $this->getMockBuilder('Mage_Webhook_Model_Dispatch_Job')
                ->disableOriginalConstructor()
                ->setMethods(array('setSubscriberId', 'setEventId', 'setStatus', 'save'))
                ->getMock();
        $job->expects($this->once())->method('setSubscriberId')->with($subId)->will($this->returnSelf());
        $job->expects($this->once())->method('setEventId')->with($eventId)->will($this->returnSelf());
        $job->expects($this->once())->method('setStatus')->with(Mage_Webhook_Model_Dispatch_Job::READY_TO_SEND)
                ->will($this->returnSelf());
        $job->expects($this->once())->method('save');

        return $job;
    }

    public function testCreateJobsNone()
    {
        $topic = 'randomTopic';
        $mapping = 'randomMapping';

        $event = $this->_createEventForJob($topic, $mapping, null, null);

        $subscribers = array();
        $this->_createSubscriberCollection($subscribers, $topic, $mapping);

        $this->_mockObject->createJobs($event);
    }

    public function testCreateJobsOne()
    {
        $topic = 'randomTopic';
        $mapping = 'randomMapping';
        $eventId = 'randomId';

        $event = $this->_createEventForJob($topic, $mapping, $eventId, 1);

        $sub1Id = 'randomSub1Id';
        $sub1   = $this->_createSubscriberForEvent($sub1Id);

        $subscribers = array($sub1);
        $this->_createSubscriberCollection($subscribers, $topic, $mapping);

        $job = $this->_createExpectedJob($sub1Id, $eventId);

        $this->_mockObject->expects($this->once())
                ->method('_newDispatchJob')
                ->will($this->returnValue($job));

        $this->_mockObject->createJobs($event);
    }

    public function testCreateJobsMany()
    {
        $topic = 'randomTopic';
        $mapping = 'randomMapping';
        $eventId = 'randomId';

        $event = $this->_createEventForJob($topic, $mapping, $eventId, 2);

        $sub1Id = 'randomSub1Id';
        $sub1   = $this->_createSubscriberForEvent($sub1Id);

        $sub2Id = 'randomSub2Id';
        $sub2   = $this->_createSubscriberForEvent($sub2Id);

        $subscribers = array($sub1, $sub2);
        $this->_createSubscriberCollection($subscribers, $topic, $mapping);

        $job1 = $this->_createExpectedJob($sub1Id, $eventId);
        $job2 = $this->_createExpectedJob($sub2Id, $eventId);

        $this->_mockObject->expects($this->exactly(2))
                ->method('_newDispatchJob')
                ->will($this->onConsecutiveCalls($job1, $job2));

        $this->_mockObject->createJobs($event);
    }
}
