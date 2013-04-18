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
class Mage_Webhook_Model_Job_DispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Job_Dispatcher
     */
    protected $_mockObject;

    /**
     * @var Mage_Webhook_Model_Transport_Interface
     */
    protected $_transportMock;

    /**
     * @var Mage_Webhook_Model_Transport_Http_Response
     */
    protected $_responseMock;

    /**
     * @var string
     */
    protected $_topic;

    /**
     * @var $objectManagerMock Magento_ObjectManager_Zend
     */
    protected $_objectManagerMock;

    public function setUp()
    {
        parent::setUp();

        $this->_topic = 'random/topic';

        $this->_mockObject = $this->getMockBuilder('Mage_Webhook_Model_Job_Dispatcher')
                                  ->disableOriginalConstructor()
                                  ->setMethods(array('_getTransport', '_getFormatterFactory', '_getFailureHandler'))
                                  ->getMock();

        $this->_transportMock = $this->getMockBuilder('Mage_Webhook_Model_Transport_Interface')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('dispatchMessage'))
                                     ->getMock();

        $this->_responseMock = $this->getMockBuilder('Mage_Webhook_Model_Transport_Http_Response')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('isSuccessful', 'getBody'))
                                     ->getMock();
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager_Zend', array(), array(), '', false);
    }

    /**
     * Tests that a job has already failed and should not be dispatched.
     */
    public function testJobAlreadyFailed()
    {
        $job = $this->_createJobMock(null, null, Mage_Webhook_Model_Dispatch_Job::FAILED);

        $this->assertEquals(false, $this->_mockObject->dispatch($job));
    }

    /**
     * Tests when a subscriber does not want the topic anymore so just
     * fail the job with special code.
     */
    public function testSubscriberNotSubscribedToTopicAnymore()
    {
        $eventMock = $this->_createEventMock();
        $subscriberMock = $this->_createSubscriberMock(false);
        $job = $this->_createJobMock($eventMock, $subscriberMock, Mage_Webhook_Model_Dispatch_Job::READY_TO_SEND);

        $job->expects($this->once())
            ->method('setStatus')
            ->with(Mage_Webhook_Model_Dispatch_Job::FAILED_NOT_SUBSCRIBED);

        $this->assertEquals(true, $this->_mockObject->dispatch($job));
    }

    /**
     * Tests that the transport was able to send the message
     */
    public function testSubscriberSendSuccess()
    {
        $format = "json";
        $mapping = "default";
        $eventMock = $this->_createEventMock($mapping);
        $subscriberMock = $this->_createSubscriberMock(true, $format);
        $job = $this->_createJobMock($eventMock, $subscriberMock, Mage_Webhook_Model_Dispatch_Job::READY_TO_SEND);

        $messageMock = $this->_createMessageMock($format, $mapping, $eventMock);

        $this->_setupTransport(true,
                               $messageMock,
                               $subscriberMock);

        $job->expects($this->once())
            ->method('setStatus')
            ->with(Mage_Webhook_Model_Dispatch_Job::SUCCESS);

        $this->assertEquals(true, $this->_mockObject->dispatch($job));
    }

    /**
     * Tests that the transport failed in sending the message and is now set
     * to retry.
     */
    public function testSubscriberSendFailureNeedToRetry()
    {
        $format = "json";
        $mapping = "default";
        $eventMock = $this->_createEventMock($mapping);
        $subscriberMock = $this->_createSubscriberMock(true, $format);
        $job = $this->_createJobMock($eventMock, $subscriberMock, Mage_Webhook_Model_Dispatch_Job::READY_TO_SEND);

        $messageMock = $this->_createMessageMock($format, $mapping, $eventMock);

        $this->_setupTransport(false,
                               $messageMock,
                               $subscriberMock);

        $job->expects($this->once())
            ->method('getRetryCount')
            ->will($this->returnValue(0));
        
        $job->expects($this->once())
            ->method('setRetryCount')
            ->with(1);

        $job->expects($this->once())
            ->method('setRetryAt')
            ->with($this->anything());
        $job->expects($this->once())
            ->method('setUpdatedAt')
            ->with($this->anything());

        $job->expects($this->once())
            ->method('setStatus')
            ->with(Mage_Webhook_Model_Dispatch_Job::RETRY);

        $this->_mockObject->expects($this->once())
            ->method('_getFailureHandler')
            ->will($this->returnValue(new Mage_Webhook_Model_Job_Retry_Handler()));

        $this->assertEquals(true, $this->_mockObject->dispatch($job));
    }

    /**
     * Tests that the transport failed in sending the message and has retried
     * way too many times so it fails.
     */
    public function testSubscriberSendFailureTooManyRetries()
    {
        $format = "json";
        $mapping = "default";
        $eventMock = $this->_createEventMock($mapping);
        $subscriberMock = $this->_createSubscriberMock(true, $format);
        $job = $this->_createJobMock($eventMock, $subscriberMock, Mage_Webhook_Model_Dispatch_Job::READY_TO_SEND);

        $messageMock = $this->_createMessageMock($format, $mapping, $eventMock);

        $this->_setupTransport(false,
                               $messageMock,
                               $subscriberMock);

        $job->expects($this->once())
            ->method('getRetryCount')
            ->will($this->returnValue(100000));
        
        $job->expects($this->once())
            ->method('setStatus')
            ->with(Mage_Webhook_Model_Dispatch_Job::FAILED);

        $this->_mockObject->expects($this->once())
            ->method('_getFailureHandler')
            ->will($this->returnValue(new Mage_Webhook_Model_Job_Retry_Handler()));

        $this->assertEquals(true, $this->_mockObject->dispatch($job));
    }

    public function testDispatchCallback()
    {
        $event = $this->getMock('Mage_Webhook_Model_Event', array('getMapping'), array(), '', false);
        $subscriber = $this->getMock('Mage_Webhook_Model_Subscriber', array('getFormat'), array(), '', false);
        $subscriber->expects($this->once())
            ->method('getFormat')
            ->will($this->returnValue('json'));

        $event->expects($this->once())
            ->method('getMapping')
            ->will($this->returnValue('default'));
        $messageMock = $this->_createMessageMock('json', 'default', $event);



        $this->_setupTransport(true, $messageMock, $subscriber);

        $result = $this->_mockObject->dispatchCallback($event, $subscriber);
        $this->assertEquals($messageMock, $result);
    }

    /**
     * @expectedException Mage_Webhook_Exception
     */
    public function testDispatchCallbackUnsuccessfull()
    {
        $event = $this->getMock('Mage_Webhook_Model_Event', array('getMapping'), array(), '', false);
        $subscriber = $this->getMock('Mage_Webhook_Model_Subscriber', array('getFormat'), array(), '', false);
        $subscriber->expects($this->once())
            ->method('getFormat')
            ->will($this->returnValue('json'));

        $event->expects($this->once())
            ->method('getMapping')
            ->will($this->returnValue('default'));
        $messageMock = $this->_createMessageMock('json', 'default', $event);

        $this->_setupTransport(false, $messageMock, $subscriber);

        $this->_mockObject->dispatchCallback($event, $subscriber);
    }

    protected function _createJobMock($event, $subscriber, $status)
    {
        $jobMock = $this->getMockBuilder('Mage_Webhook_Model_Job_Interface')
                        ->disableOriginalConstructor()
                        ->setMethods(array(
                            'getEvent',
                            'getSubscriber',
                            'getRetryCount',
                            'getStatus',
                            'setStatus',
                            'setRetryAt',
                            'setRetryCount',
                            'setUpdatedAt',
                            'save'
                        ))
                        ->getMock();

        if (!is_null($event)) {
            $jobMock->expects($this->once())
                    ->method('getEvent')
                    ->will($this->returnValue($event));

            $jobMock->expects($this->once())
                    ->method('save');
        }

        if (!is_null($subscriber)) {
            $jobMock->expects($this->once())
                    ->method('getSubscriber')
                    ->will($this->returnValue($subscriber));
        }

        $jobMock->expects($this->once())
                ->method('getStatus')
                ->will($this->returnValue($status));

        return $jobMock;
    }

    protected function _createEventMock($mapping = null)
    {
        $eventMock = $this->getMockBuilder('Mage_Webhook_Model_Event')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getTopic', 'getMapping'))
                          ->getMock();

        $eventMock->expects($this->once())
                  ->method('getTopic')
                  ->will($this->returnValue($this->_topic));

        if (!is_null($mapping)) {
            $eventMock->expects($this->once())
                      ->method('getMapping')
                      ->will($this->returnValue($mapping));
        }

        return $eventMock;
    }

    protected function _createSubscriberMock($isSubscribed, $format = null)
    {
        $subscriberMock = $this->getMockBuilder('Mage_Webhook_Model_Subscriber')
                               ->disableOriginalConstructor()
                               ->setMethods(array('isSubscribedToTopic', 'getFormat'))
                               ->getMock();

        $subscriberMock->expects($this->once())
                       ->method('isSubscribedToTopic')
                       ->will($this->returnValue($isSubscribed));

        if ($isSubscribed) {
            $subscriberMock->expects($this->once())
                           ->method('getFormat')
                           ->will($this->returnValue($format));
        }

        return $subscriberMock;
    }

    protected function _createMessageMock($format, $mapping, $event)
    {
        $formatterMock = $this->_createFormatterMock($format, $mapping);

        $messageMock = $this->getMockBuilder('Mage_Webhook_Model_Message')
                            ->disableOriginalConstructor()
                            ->getMock();
        $formatterMock->expects($this->once())
                    ->method('format')
                    ->with($event)
                    ->will($this->returnValue($messageMock));

        return $messageMock;
    }

    protected function _createFormatterMock($format, $mapping)
    {
        $factory = $this->getMockBuilder('Mage_Webhook_Model_Formatter_Factory')
                                     ->disableOriginalConstructor()
                                     ->setMethods(array('getFormatterFactory'))
                                     ->getMock();
        $this->_mockObject->expects($this->once())
                          ->method('_getFormatterFactory')
                          ->will($this->returnValue($factory));

        $formatterFactory = $this->getMockBuilder('Mage_Webhook_Model_FormatterFactory')
                                            ->disableOriginalConstructor()
                                            ->setMethods(array('getFormatter'))
                                            ->getMock();
        $factory->expects($this->once())
                       ->method('getFormatterFactory')
                       ->with($format)
                       ->will($this->returnValue($formatterFactory));

        $formatterMock = $this->getMockBuilder('Mage_Webhook_Model_Formatter')
                            ->disableOriginalConstructor()
                            ->setMethods(array('format', 'decode'))
                            ->getMock();
        $formatterFactory->expects($this->once())
                             ->method('getFormatter')
                             ->with($mapping)
                             ->will($this->returnValue($formatterMock));

        $this->_mockObject->expects($this->once())
                          ->method('_getFormatterFactory')
                          ->will($this->returnValue($factory));

        return $formatterMock;
    }

    protected function _setupTransport($status, $message, $subscriber)
    {
        $this->_mockObject->expects($this->once())
                          ->method('_getTransport')
                          ->will($this->returnValue($this->_transportMock));
        $this->_transportMock->expects($this->once())
                             ->method('dispatchMessage')
                             ->with($message, $subscriber)
                             ->will($this->returnValue($this->_responseMock));
        $this->_responseMock->expects($this->once())
                            ->method('isSuccessful')
                            ->will($this->returnValue($status));
        $this->_responseMock->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue('{}'));

    }
}
