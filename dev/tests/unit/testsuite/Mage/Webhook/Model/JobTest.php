<?php
/**
 * Mage_Webhook_Model_Job
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
class Mage_Webhook_Model_JobTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Mage_Webhook_Model_Job */
    protected $_job;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockEventFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockSubscrFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockContext;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockEvent;

    public function setUp()
    {
        $this->_mockEventFactory = $this->getMockBuilder('Mage_Webhook_Model_Event_Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockSubscrFactory = $this->getMockBuilder('Mage_Webhook_Model_Subscription_Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockContext = $this->getMockBuilder('Mage_Core_Model_Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockEvent = $this->getMockBuilder('Mage_Webhook_Model_Event')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext)
            )
            ->setMethods(array('_init', 'save'))
            ->getMock();
    }

    public function testConstructorWithData()
    {
        $eventId = 'some event test id';
        $subscriptionId = 'some subscription test id';

        $mockEvent = $this->getMockBuilder('Mage_Webhook_Model_Event')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEvent->expects($this->once())
            ->method('getId')
            ->withAnyParameters()
            ->will($this->returnValue($eventId));

        $mockSubscription = $this->getMockBuilder('Mage_Webhook_Model_Subscription')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSubscription->expects($this->once())
            ->method('getId')
            ->withAnyParameters()
            ->will($this->returnValue($subscriptionId));

        $data = array('event'        => $mockEvent,
                      'subscription' => $mockSubscription);

        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext,
                      null,
                      null,
                      $data)
            )
            ->setMethods(array('_init'))
            ->getMock();

        $this->assertSame($eventId, $this->_job->getEventId());
        $this->assertSame($subscriptionId, $this->_job->getSubscriptionId());
    }

    public function testGetEventWithEventIdInData()
    {
        $eventId = 'some event id';
        $event = 'some event';
        $data = array('event_id' => $eventId);

        $this->_mockEventFactory->expects($this->once())
            ->method('createEmpty')
            ->withAnyParameters()
            ->will($this->returnValue($this->_mockEvent));

        $this->_mockEvent->expects($this->once())
            ->method('load')
            ->with($this->equalTo($eventId))
            ->will($this->returnValue($event));

        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext,
                      null,
                      null,
                      $data)
            )
            ->setMethods(array('_init'))
            ->getMock();

        $this->assertSame($event, $this->_job->getEvent());
    }

    public function testGetEventWithEventInData()
    {
        $mockEvent = $this->getMockBuilder('Mage_Webhook_Model_Event')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('getId')
            ->withAnyParameters()
            ->will($this->returnValue('some event id'));

        $data = array('event' => $mockEvent);

        $this->_mockEventFactory->expects($this->never())
            ->method('createEmpty');

        $this->_mockEvent->expects($this->never())
            ->method('load');

        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext,
                      null,
                      null,
                      $data)
            )
            ->setMethods(array('_init'))
            ->getMock();

        $this->assertSame($mockEvent, $this->_job->getEvent());
    }

    public function testGetSubscWithSubscrIdInData()
    {
        $subscrId = 'some subscription id';
        $subscr = 'some subscription';
        $data = array('subscription_id' => $subscrId);

        $mockSubscription = $this->getMockBuilder('Mage_Webhook_Model_Subscription')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSubscription->expects($this->once())
            ->method('load')
            ->with($subscrId)
            ->will($this->returnValue($subscr));

        $this->_mockSubscrFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($mockSubscription));

        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext,
                      null,
                      null,
                      $data)
            )
            ->setMethods(array('_init'))
            ->getMock();

        $this->assertSame($subscr, $this->_job->getSubscription());
    }

    public function testGetSubscWithSubscrInData()
    {
        $subscriptionId = 'some subscription id';
        $mockSubscription = $this->getMockBuilder('Magento_PubSub_Subscription')
            ->disableOriginalConstructor()
            ->setMethods(
                array('getId')
            )
            ->getMock();
        $mockSubscription->expects($this->once())
            ->method('getId')
            ->withAnyParameters()
            ->will($this->returnValue($subscriptionId));
        $data = array('subscription' => $mockSubscription);

        $this->_mockSubscrFactory->expects($this->never())
            ->method('create');

        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext,
                      null,
                      null,
                      $data)
            )
            ->setMethods(array('_init'))
            ->getMock();

        $this->assertSame($mockSubscription, $this->_job->getSubscription());
    }

    public function testHandleResponseSuccess()
    {
        $this->_job->expects($this->once())
            ->method('save')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $response = $this->getMockBuilder('Magento_Outbound_Transport_Http_Response')
            ->disableOriginalConstructor()
            ->setMethods(array('isSuccessful'))
            ->getMock();

        $response->expects($this->once())
            ->method('isSuccessful')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $this->_job->handleResponse($response);

        $this->assertEquals(Magento_PubSub_JobInterface::SUCCESS, $this->_job->getStatus());
    }

    public function testHandleResponseFailure()
    {
        $this->_job->expects($this->any())
            ->method('save')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $response = $this->getMockBuilder('Magento_Outbound_Transport_Http_Response')
            ->disableOriginalConstructor()
            ->setMethods(array('isSuccessful'))
            ->getMock();

        $response->expects($this->any())
            ->method('isSuccessful')
            ->withAnyParameters()
            ->will($this->returnValue(false));

        $count = 0;
        while ($count < 8) {
            $this->_job->handleResponse($response);
            $this->assertEquals(Magento_PubSub_JobInterface::RETRY, $this->_job->getStatus());
            $count++;
        }
        $this->_job->handleResponse($response);
        $this->assertEquals(Magento_PubSub_JobInterface::FAILED, $this->_job->getStatus());
    }

    public function testGetNoEvent()
    {
        $this->assertNull($this->_job->getEvent());
    }

    public function testGetNoSubscription()
    {
        $this->assertNull($this->_job->getSubscription());
    }

    /**
     * Tests that a job which has failed for the first 8 times is given another
     * chance.
     */
    public function testJobGiven8Retries()
    {
        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext)
            )
            ->setMethods(array('_init', 'save', 'getRetryCount', 'setUpdatedAt', 'setStatus'))
            ->getMock();

        $retryCount = 8;
        $this->_job->expects($this->exactly($retryCount))
            ->method('getRetryCount')
            ->will($this->onConsecutiveCalls(0, 1, 2, 3, 4, 5, 6, 7));

        $this->_job->expects($this->exactly($retryCount))
            ->method('setUpdatedAt')
            ->with($this->anything());
        $this->_job->expects($this->exactly($retryCount))
            ->method('setStatus')
            ->with(Magento_PubSub_JobInterface::RETRY);

        for ($count = 0; $count < $retryCount; $count++) {
            $this->_job->handleFailure();
        }
    }

    /**
     * Tests that a job which has failed over 8 times is marked as failed.
     */
    public function testJobFailAfter8Retries()
    {
        $this->_job = $this->getMockBuilder('Mage_Webhook_Model_Job')
            ->setConstructorArgs(
                array($this->_mockEventFactory,
                      $this->_mockSubscrFactory,
                      $this->_mockContext)
            )
            ->setMethods(array('_init', 'save', 'getRetryCount', 'setStatus'))
            ->getMock();

        $this->_job->expects($this->exactly(1))
            ->method('getRetryCount')
            ->will($this->returnValue(8));

        $this->_job->expects($this->exactly(1))
            ->method('setStatus')
            ->with(Magento_PubSub_JobInterface::FAILED);

        $this->_job->handleFailure();
    }
}