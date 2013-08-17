<?php
/**
 * Magento_PubSub_Event_QueueHandler
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
class Magento_PubSub_Event_QueueHandlerTests extends PHPUnit_Framework_TestCase
{
    /**
     * mock endpoint url
     */
    const ENDPOINT_URL = 'http://localhost/';

    /**
     * @var Magento_PubSub_Event_QueueHandler
     */
    protected $_model;

    public function setUp()
    {
        /** @var Mage_Webhook_Model_Resource_Event_Collection $eventCollection */
        $eventCollection = Mage::getObjectManager()->create('Mage_Webhook_Model_Resource_Event_Collection')
            ->addFieldToFilter('status', Magento_PubSub_EventInterface::READY_TO_SEND);
        /** @var array $event */
        $events = $eventCollection->getItems();
        /** @var Mage_Webhook_Model_Event $event */
        foreach ($events as $event) {
            $event->markAsProcessed();
            $event->save();
        }

        /** @var $factory Mage_Webhook_Model_Event_Factory */
        $factory = Mage::getObjectManager()->create('Magento_PubSub_Event_FactoryInterface');

        /** @var $event Mage_Webhook_Model_Event */
        $factory->create('testinstance/created', array(
            'testKey1' => 'testValue1'
        ))->save();

        $factory->create('testinstance/updated', array(
            'testKey2' => 'testValue2'
        ))->save();

        $endpoint = Mage::getObjectManager()->create('Mage_Webhook_Model_Endpoint')
            ->setEndpointUrl(self::ENDPOINT_URL)
            ->setFormat('json')
            ->setAuthenticationType('hmac')
            ->setTimeoutInSecs('20')
            ->save();

        Mage::getObjectManager()->configure(array(
            'Mage_Core_Model_Config_Base' => array(
                'parameters' => array(
                    'sourceData' => __DIR__ . '/../_files/config.xml',
                ),
            ),
            'Mage_Webhook_Model_Resource_Subscription' => array(
                'parameters' => array(
                    'config' => array('instance' => 'Mage_Core_Model_Config_Base'),
                ),
            )
        ));

        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getObjectManager()->create('Mage_Webhook_Model_Subscription');
        $subscription->setData(
            array(
                'name' => 'test',
                'status' => Mage_Webhook_Model_Subscription::STATUS_INACTIVE,
                'version' => 1,
                'alias' => 'test',
                'topics' => array(
                    'testinstance/created',
                    'testinstance/updated'
                ),
            ))->save();

        // Simulate activating of the subscription
        $webApiUser = Mage::getObjectManager()->create('Mage_Webapi_Model_Acl_User')
            ->setData('api_key', 'test')
            ->setData('secret', 'secret')
            ->save();
        $endpoint->setApiUserId($webApiUser->getId())
            ->save();
        $subscription->setEndpointId($endpoint->getId())
            ->setStatus(Mage_Webhook_Model_Subscription::STATUS_ACTIVE)
            ->save();;

        $this->_model = Mage::getObjectManager()->get('Magento_PubSub_Event_QueueHandler');
    }

    /**
     * Test the main flow of event queue handling
     */
    public function testHandle()
    {
        $this->_model->handle();
        /** @var $queue Magento_PubSub_Job_QueueReaderInterface */
        $queue = Mage::getObjectManager()->get('Magento_PubSub_Job_QueueReaderInterface');

        /* First EVENT */
        $job = $queue->poll();
        $this->assertNotNull($job);
        $this->assertInstanceOf('Magento_PubSub_JobInterface', $job);
        $event = $job->getEvent();
        $subscription = $job->getSubscription();

        $this->assertEquals('testinstance/created', $event->getTopic());
        $this->assertEquals(array('testKey1' => 'testValue1'), $event->getBodyData());

        $this->assertEquals(self::ENDPOINT_URL, $subscription->getEndpointUrl());
        $this->assertEquals(20, $subscription->getTimeoutInSecs());

        /* Second EVENT */
        $job = $queue->poll();
        $this->assertNotNull($job);
        $event = $job->getEvent();
        $subscription = $job->getSubscription();

        $this->assertEquals('testinstance/updated', $event->getTopic());
        $this->assertEquals(array('testKey2' => 'testValue2'), $event->getBodyData());

        $this->assertEquals(self::ENDPOINT_URL, $subscription->getEndpointUrl());
        $this->assertEquals(20, $subscription->getTimeoutInSecs());

        /* No more EVENTS */
        $job = $queue->poll();
        $this->assertNull($job);
    }
}