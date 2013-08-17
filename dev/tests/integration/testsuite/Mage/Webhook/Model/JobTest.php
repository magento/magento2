<?php
/**
 * Mage_Webhook_Model_Job
 *
 * @magentoDbIsolation enabled
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
class Mage_Webhook_Model_JobTest extends PHPUnit_Framework_TestCase
{
    /**
     * mock endpoint url
     */
    const ENDPOINT_URL = 'http://localhost/';
    const SUCCESS_RESPONSE = 200;
    const FAILURE_RESPONSE = 404;

    /**
     * @var Mage_Webhook_Model_Job
     */
    protected $_job;

    public function setUp()
    {
        $this->_job = Mage::getObjectManager()->create('Mage_Webhook_Model_Job');
    }

    public function testConstruct()
    {
        $event = Mage::getModel('Mage_Webhook_Model_Event')
            ->setDataChanges(true)
            ->save();
        $eventId = $event->getId();
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription')
            ->setDataChanges(true)
            ->save();
        $subscriptionId = $subscription->getId();

        $this->_job->setEvent($event);
        $this->_job->setSubscription($subscription);
        $this->_job->_construct();

        $this->assertEquals($eventId, $this->_job->getEventId());
        $this->assertEquals($subscriptionId, $this->_job->getSubscriptionId());
    }

    public function testGetEventById()
    {
        $eventId = Mage::getModel('Mage_Webhook_Model_Event')
            ->setDataChanges(true)
            ->save()
            ->getId();
        $this->_job->setData('event_id', $eventId);
        $this->assertEquals($eventId, $this->_job->getEvent()->getId());
    }

    public function testGetEvent()
    {
        $event = Mage::getModel('Mage_Webhook_Model_Event')
            ->setDataChanges(true)
            ->save();
        $this->_job->setData('event', $event);
        $this->assertEquals($event, $this->_job->getEvent());
    }

    public function testGetSubscriptionById()
    {
        $subscriptionId = Mage::getModel('Mage_Webhook_Model_Subscription')
            ->setDataChanges(true)
            ->save()
            ->getId();
        $this->_job->setData('subscription_id', $subscriptionId);
        $this->assertEquals($subscriptionId, $this->_job->getSubscription()->getId());
    }

    public function testGetSubscription()
    {
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription')
            ->setDataChanges(true)
            ->save();
        $this->_job->setData('subscription', $subscription);
        $this->assertEquals($subscription, $this->_job->getSubscription());
    }

    public function testGetNonexistent()
    {
        $this->assertEquals(null, $this->_job->getEvent());
        $this->assertEquals(null, $this->_job->getSubscription());
    }

    public function testHandleResponseSuccess()
    {
        $subscriptionId = Mage::getModel('Mage_Webhook_Model_Subscription')
            ->setDataChanges(true)
            ->save()
            ->getId();
        $eventId = Mage::getModel('Mage_Webhook_Model_Event')
            ->setDataChanges(true)
            ->save()
            ->getId();
        $this->_job->setData('subscription_id', $subscriptionId);
        $this->_job->setData('event_id', $eventId);

        $response = new Magento_Outbound_Transport_Http_Response(
            new Zend_Http_Response(self::SUCCESS_RESPONSE, array()));

        $this->_job->handleResponse($response);
        $this->assertEquals(Magento_PubSub_JobInterface::SUCCESS, $this->_job->getStatus());
    }

    public function testHandleResponseRetry()
    {
        $subscriptionId = Mage::getModel('Mage_Webhook_Model_Subscription')
            ->setDataChanges(true)
            ->save()
            ->getId();
        $this->_job->setData('subscription_id', $subscriptionId);

        $eventId = Mage::getModel('Mage_Webhook_Model_Event')
            ->setDataChanges(true)
            ->save()
            ->getId();
        $this->_job->setData('event_id', $eventId);

        $response = new Magento_Outbound_Transport_Http_Response(
            new Zend_Http_Response(self::FAILURE_RESPONSE, array()));
        $this->_job->handleResponse($response);
        $this->assertEquals(Magento_PubSub_JobInterface::RETRY, $this->_job->getStatus());
    }

    public function testHandleFailure()
    {
        $count = 1;
        while ($count <= 8) {
            $this->_job->handleFailure();
            $this->assertEquals(Magento_PubSub_JobInterface::RETRY, $this->_job->getStatus());
            $this->assertEquals($count, $this->_job->getRetryCount());
            $count++;
        }
        $this->_job->handleFailure();
        $this->assertEquals(Magento_PubSub_JobInterface::FAILED, $this->_job->getStatus());
    }
}