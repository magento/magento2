<?php
/**
 * Mage_Webhook_Service_SubscriptionV1
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
 * @category    Magento
 * @package     Mage_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Service_SubscriptionV1Test extends PHPUnit_Framework_TestCase
{
    /** Constants for validation of field data */
    const VALUE_NAME = 'Name of the Subscriber';
    const VALUE_ALIAS = 'test alias';
    const VALUE_ENDPOINT_URL = 'http://localhost/reach_us_here';

    const KEY_ENDPOINT_URL = Mage_Webhook_Model_SubscriptionTest::KEY_ENDPOINT_URL;
    const KEY_NAME = Mage_Webhook_Model_SubscriptionTest::KEY_NAME;
    const KEY_ALIAS = Mage_Webhook_Model_SubscriptionTest::KEY_ALIAS;
    const KEY_API_USER_ID = Mage_Webhook_Model_SubscriptionTest::KEY_API_USER_ID;
    const KEY_TOPICS = 'topics';
    const KEY_STATUS = Mage_Webhook_Model_SubscriptionTest::KEY_STATUS;

    /** @var  array */
    private $_subscriptionData;

    /** @var  int */
    private $_apiUserId;

    public function setUp()
    {
        $userContext = array(
            'email'     => 'email@example.com',
            'key'       => 'key',
            'secret'    => 'secret',
        );
        /** @var Mage_Webhook_Model_Webapi_User_Factory $webapiUserFactory */
        $webapiUserFactory = Mage::getModel('Mage_Webhook_Model_Webapi_User_Factory');
        $this->_apiUserId = $webapiUserFactory->createUser($userContext, array('webhook/create'));

        $this->_subscriptionData = array(
            self::KEY_ALIAS => self::VALUE_ALIAS,
            self::KEY_NAME => self::VALUE_NAME,
            self::KEY_ENDPOINT_URL => self::VALUE_ENDPOINT_URL,
            self::KEY_API_USER_ID => $this->_apiUserId,
            // TODO: Right now if we check for topic permissions it will fail.  Not sure why.
            //self::KEY_TOPICS => array('webhook/create'),
        );
    }

    public function tearDown()
    {
        /** @var Mage_Webapi_Model_Acl_User $user */
        $user = Mage::getModel('Mage_Webapi_Model_Acl_User');
        $user->load($this->_apiUserId);
        $user->delete();
    }

    public function testCreate()
    {
        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $resultData = $service->create($this->_subscriptionData);

        // verify
        $this->assertTrue($resultData['subscription_id'] > 0);
        $this->assertEquals(self::VALUE_ENDPOINT_URL, $resultData[self::KEY_ENDPOINT_URL]);
    }

    /**
     * No user exists yet, so we don't expect invalid topics to be identified at this point.
     */
    public function testCreateInvalidTopicsNoUser()
    {
        $this->_subscriptionData[self::KEY_TOPICS] = array('invalid/topic', 'also/invalid/topic');
        unset($this->_subscriptionData[self::KEY_API_USER_ID]);

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $resultData = $service->create($this->_subscriptionData);

        $this->assertTrue($resultData['subscription_id'] > 0);
    }

    /**
     * @expectedException Mage_Webhook_Exception
     * @expectedExceptionMessage not authorized
     */
    public function testCreateInvalidTopicsWithUser()
    {
        $this->_subscriptionData[self::KEY_TOPICS] = array('invalid/topic', 'also/invalid/topic');
        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $resultData = $service->create($this->_subscriptionData);

        $this->assertTrue($resultData['subscription_id'] > 0);
    }

    public function testGet()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->save();

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $resultData = $service->get($subscription->getId());

        $this->assertEquals($subscription->getId(), $resultData['subscription_id']);
        $this->assertEquals(self::VALUE_ENDPOINT_URL, $resultData[self::KEY_ENDPOINT_URL]);
    }

    /**
     * @expectedException Mage_Webhook_Exception
     * @expectedExceptionMessage 0
     */
    public function testGetNotFound()
    {
        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $service->get(0);
    }

    public function testGetAll()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $first = Mage::getModel('Mage_Webhook_Model_Subscription');
        $first->setData($this->_subscriptionData);
        $first->save();

        /** @var Mage_Webhook_Model_Subscription $subscription */
        $second = Mage::getModel('Mage_Webhook_Model_Subscription');
        $second->setData($this->_subscriptionData);
        $second->save();

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $subscriptions = $service->getAll($this->_apiUserId);

        $this->assertEquals($first->getId(), $subscriptions[0]['subscription_id']);
        $this->assertEquals($second->getId(), $subscriptions[1]['subscription_id']);
    }

    public function testUpdate()
    {
        $newUrl = self::VALUE_ENDPOINT_URL . '/plus/this';
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->save();
        $subscriptionData = $subscription->getData();
        $subscriptionData[self::KEY_ENDPOINT_URL] = $newUrl;

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $resultData = $service->update($subscriptionData);

        $this->assertEquals($subscription->getId(), $resultData['subscription_id']);
        $this->assertEquals($newUrl, $resultData[self::KEY_ENDPOINT_URL]);
        $this->assertEquals(self::VALUE_NAME, $resultData[self::KEY_NAME]);
    }

    public function testDelete()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->save();

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $resultData = $service->delete($subscription->getId());

        $this->assertEquals($subscription->getId(), $resultData['subscription_id']);
        $this->assertEquals(self::VALUE_ENDPOINT_URL, $resultData[self::KEY_ENDPOINT_URL]);
        $this->assertEquals(self::VALUE_NAME, $resultData[self::KEY_NAME]);


        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->load($resultData['subscription_id']);
        $this->assertEquals(0, $subscription->getId());
    }

    public function testValidateOwnershipPass()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->save();

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $service->validateOwnership($this->_apiUserId, $subscription->getId());
    }

    /**
     * @expectedException Mage_Webhook_Exception
     * @expectedExceptionMessage permission
     */
    public function testValidateOwnershipFail()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->save();

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $service->validateOwnership($this->_apiUserId + 42, $subscription->getId());
    }

    public function testActivate()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->save();
        // verify initial state
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_INACTIVE, $subscription->getStatus());

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $subscriptionData = $service->activate($subscription->getId());

        // verify change
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_ACTIVE, $subscriptionData[self::KEY_STATUS]);
        $subscription->load($subscription->getId());
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_ACTIVE, $subscription->getStatus());
    }

    public function testDeactivate()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->setStatus(Mage_Webhook_Model_Subscription::STATUS_ACTIVE);
        $subscription->save();
        // verify initial state
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_ACTIVE, $subscription->getStatus());

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $subscriptionData = $service->deactivate($subscription->getId());

        // verify change
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_INACTIVE, $subscriptionData[self::KEY_STATUS]);
        $subscription->load($subscription->getId());
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_INACTIVE, $subscription->getStatus());
    }

    public function testRevoke()
    {
        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getModel('Mage_Webhook_Model_Subscription');
        $subscription->setData($this->_subscriptionData);
        $subscription->setStatus(Mage_Webhook_Model_Subscription::STATUS_ACTIVE);
        $subscription->save();
        // verify initial state
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_ACTIVE, $subscription->getStatus());

        /** @var Mage_Webhook_Service_SubscriptionV1 $service */
        $service = Mage::getModel('Mage_Webhook_Service_SubscriptionV1');
        $subscriptionData = $service->revoke($subscription->getId());

        // verify change
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_REVOKED, $subscriptionData[self::KEY_STATUS]);
        $subscription->load($subscription->getId());
        $this->assertEquals(Mage_Webhook_Model_Subscription::STATUS_REVOKED, $subscription->getStatus());
    }
}
