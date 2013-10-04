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
 * @category    Magento
 * @package     Magento_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model;

/**
 * \Magento\Webhook\Model\Subscription
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Constant strings representing hooks in the config files
     */
    const HOOK_IN_CONFIG = 'test/hook';
    const HOOK_NOT_IN_CONFIG = 'test/hook_not_in_config';
    const OTHER_HOOK = 'test/two';

    /**
     * Constants for setting/getting data
     */
    const VALUE_NAME = 'subscription name';
    const VALUE_ALIAS = 'sub_alias';
    const VALUE_ENDPOINT_URL = 'http://localhost/endpoint';
    const VALUE_ENDPOINT_ID = 'endpoint_id_value';
    const VALUE_FORMAT = 'json';
    const VALUE_STATUS = \Magento\Webhook\Model\Subscription::STATUS_INACTIVE;
    const VALUE_AUTHENTICATION_TYPE = 'hmac';
    const VALUE_API_USER_ID = null;
    const VALUE_REG_MECH = 'registration_mechanism';
    const VALUE_UPDATED_AT = 'Five minutes ago.';
    const VALUE_TIMEOUT_IN_SECS = '30';

    const KEY_NAME = 'name';
    const KEY_ALIAS = 'alias';
    const KEY_ENDPOINT_URL = \Magento\Webhook\Model\Subscription::FIELD_ENDPOINT_URL;
    const KEY_FORMAT = \Magento\Webhook\Model\Subscription::FIELD_FORMAT;
    const KEY_STATUS = 'status';
    const KEY_API_USER_ID = \Magento\Webhook\Model\Subscription::FIELD_API_USER_ID;
    const KEY_AUTHENTICATION_TYPE = \Magento\Webhook\Model\Subscription::FIELD_AUTHENTICATION_TYPE;
    const KEY_TIMEOUT_IN_SECS = \Magento\Webhook\Model\Subscription::FIELD_TIMEOUT_IN_SECS;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        // Clean out the cache
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Acl\CacheInterface $aclCache */
        $aclCache = $this->_objectManager->get('Magento\Acl\CacheInterface');
        $aclCache->clean();
        // add new hooks for the tests

        /** @var $configModel \Magento\Core\Model\Config */
        $configModel = $this->_objectManager->get('Magento\Core\Model\Config');
        $configModel->setNode('global/webhook/webhooks/test/hook/label', 'Test Hook');
        $configModel->setNode('global/webhook/webhooks/test/two/label', 'Test Hook Two');
    }

    protected function tearDown()
    {
        // Clean out the cache
        /** @var \Magento\Acl\CacheInterface $aclCache */
        $aclCache = $this->_objectManager->get('Magento\Acl\CacheInterface');
        $aclCache->clean();
    }

    public function testSetGetHooks()
    {

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $this->assertEmpty($subscription->getTopics(),
            "New subscription shouldn't be subscribed on any hooks.");

        // Get topics returns all topics that were set, both valid and invalid, before save
        $topics = array(self::HOOK_IN_CONFIG, self::HOOK_NOT_IN_CONFIG);
        $subscription->setTopics($topics);
        $this->assertEquals($topics, $subscription->getTopics());

        $subscription->save();

        // All, and only, topics stored in config should persist after save
        $loadedSubscription = $this->_getSubscription($subscription->getId());
        $this->assertEquals(array(self::HOOK_IN_CONFIG), $loadedSubscription->getTopics());

        $this->assertEquals(array('test/hook'), $loadedSubscription->getTopics());

        // cleanup
        $subscription->delete();
    }

    public function testHasTopic()
    {
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setTopics(array(self::HOOK_IN_CONFIG))
            ->save();

        $this->assertTrue($subscription->hasTopic(self::HOOK_IN_CONFIG));
        $this->assertFalse($subscription->hasTopic(self::HOOK_NOT_IN_CONFIG));
    }

    public function testActivate()
    {
        //setup
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setStatus(\Magento\Webhook\Model\Subscription::STATUS_INACTIVE)
            ->save();

        //action
        $subscription->activate();

        //verify
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE, $subscription->getStatus());
        $subscriptionInDb = $this->_getSubscription($subscription->getId());
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_INACTIVE, $subscriptionInDb->getStatus());
    }

    public function testDeactivate()
    {
        //setup
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->save();

        //action
        $subscription->deactivate();

        //verify
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_INACTIVE, $subscription->getStatus());
        $subscriptionInDb = $this->_getSubscription($subscription->getId());
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE, $subscriptionInDb->getStatus());
    }

    public function testRevoke()
    {
        //setup
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->save();

        //action
        $subscription->revoke();

        //verify
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_REVOKED, $subscription->getStatus());
        $subscriptionInDb = $this->_getSubscription($subscription->getId());
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE, $subscriptionInDb->getStatus());
    }

    public function testFindRestrictedTopics()
    {
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');

        /** @var \Magento\Webhook\Model\Webapi\User\Factory $webapiUserFactory */
        $webapiUserFactory = $this->_objectManager->create('Magento\Webhook\Model\Webapi\User\Factory');
        $userContext = array(
            'key' => 'some_key_value',
            'secret' => 'shh',
            'company' => 'Corporate Corporations Inc.',
            'email' => 'email.address@email.com'
        );

        $allowedTopics = array(
            'webhook/get',
        );
        $restrictedTopics = array(
            'customer/get',
        );
        $allTopics = array_merge($allowedTopics, $restrictedTopics);

        $webapiUserId = $webapiUserFactory->createUser($userContext, $allowedTopics);
        $subscription->setApiUserId($webapiUserId)
            ->setTopics($allTopics)
            ->save();

        $retrievedTopics = $subscription->findRestrictedTopics();
        $this->assertEquals($restrictedTopics, $retrievedTopics);
    }

    public function testUpdatingHooks()
    {
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setTopics(array(self::HOOK_IN_CONFIG))
            ->save();

        // Sanity check
        $loadedSubscription = $this->_getSubscription($subscription->getId());
        $this->assertEquals(array(self::HOOK_IN_CONFIG), $loadedSubscription->getTopics());

        $subscription->setTopics(array(self::OTHER_HOOK))
            ->save();

        // Verify we only have the new hook
        $loadedSubscription = $this->_getSubscription($subscription->getId());
        $this->assertEquals(array(self::OTHER_HOOK), $loadedSubscription->getTopics());
    }

    public function testGetUser()
    {
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');

        // Test getUser
        $userContext = array(
            'key' => 'some_other_key_value',
            'secret' => 'shh1',
            'company' => 'Corporate Corporations Inc.',
            'email' => 'email.address@email.com'
        );
        /** @var \Magento\Webhook\Model\User\Factory $userFactory */
        $userFactory = $this->_objectManager->create('Magento\Webhook\Model\User\Factory');
        /** @var \Magento\Webhook\Model\Webapi\User\Factory $webapiUserFactory */
        $webapiUserFactory = $this->_objectManager->create('Magento\Webhook\Model\Webapi\User\Factory');

        $userId = $webapiUserFactory->createUser($userContext, array());
        $user = $userFactory->create(array('webapiUserId' => $userId));
        $subscription->setApiUserId($userId);
        $this->assertEquals($user, $subscription->getUser());
    }

    public function testGetUserWhenNoneAssigned()
    {
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');

        $this->assertNull($subscription->getUser());
    }

    public function testSetData()
    {

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');

        $subscription->setData('key', 'value');
        $this->assertEquals('value', $subscription->getData('key'));

        $keyArray = array(
            \Magento\Webhook\Model\Subscription::FIELD_ENDPOINT_URL         => self::VALUE_ENDPOINT_URL,
            \Magento\Webhook\Model\Subscription::FIELD_FORMAT               => self::VALUE_FORMAT,
            \Magento\Webhook\Model\Subscription::FIELD_AUTHENTICATION_TYPE  => self::VALUE_AUTHENTICATION_TYPE,
            \Magento\Webhook\Model\Subscription::FIELD_API_USER_ID          => self::VALUE_API_USER_ID,
        );

        $subscription->setData($keyArray);
        $this->assertEquals(self::VALUE_ENDPOINT_URL, $subscription->getEndpointUrl());
        $this->assertEquals(self::VALUE_FORMAT, $subscription->getFormat());
        $this->assertEquals(self::VALUE_AUTHENTICATION_TYPE, $subscription->getAuthenticationType());
        $this->assertEquals(self::VALUE_API_USER_ID, $subscription->getApiUserId());


        // Clear data to test different setting logic
        $subscription->setData(array(null, null, null, null));

        foreach ($keyArray as $key => $value) {
            $subscription->setData($key, $value);
        }

        $this->assertEquals(self::VALUE_ENDPOINT_URL, $subscription->getEndpointUrl());
        $this->assertEquals(self::VALUE_FORMAT, $subscription->getFormat());
        $this->assertEquals(self::VALUE_AUTHENTICATION_TYPE, $subscription->getAuthenticationType());
        $this->assertEquals(self::VALUE_API_USER_ID, $subscription->getApiUserId());

    }
    
    public function testSetGetMethods()
    {

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');

        $subscription->setAlias(self::VALUE_ALIAS);
        $subscription->setEndpointId(self::VALUE_ENDPOINT_ID);
        $subscription->setName(self::VALUE_NAME);
        $subscription->setRegistrationMechanism(self::VALUE_REG_MECH);
        $subscription->setStatus(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE);
        $subscription->setUpdatedAt(self::VALUE_UPDATED_AT);
        $subscription->setApiUserId(self::VALUE_API_USER_ID);
        $subscription->setAuthenticationType(self::VALUE_AUTHENTICATION_TYPE);
        $subscription->setEndpointUrl(self::VALUE_ENDPOINT_URL);
        $subscription->setFormat(self::VALUE_FORMAT);

        $this->assertEquals(self::VALUE_ALIAS, $subscription->getAlias());
        $this->assertEquals(self::VALUE_ENDPOINT_ID, $subscription->getEndpointId());
        $this->assertEquals(self::VALUE_NAME, $subscription->getName());
        $this->assertEquals(self::VALUE_REG_MECH, $subscription->getRegistrationMechanism());
        $this->assertEquals(\Magento\PubSub\SubscriptionInterface::STATUS_ACTIVE, $subscription->getStatus());
        $this->assertEquals(self::VALUE_UPDATED_AT, $subscription->getUpdatedAt());
        $this->assertEquals(self::VALUE_API_USER_ID, $subscription->getApiUserId());
        $this->assertEquals(self::VALUE_AUTHENTICATION_TYPE, $subscription->getAuthenticationType());
        $this->assertEquals(self::VALUE_ENDPOINT_ID, $subscription->getEndpointId());
        $this->assertEquals(self::VALUE_FORMAT, $subscription->getFormat());


    }

    public function testSettingData()
    {
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setData(self::KEY_NAME, self::VALUE_NAME);
        $subscription->setData(self::KEY_ALIAS, self::VALUE_ALIAS);
        $subscription->setData(self::KEY_ENDPOINT_URL, self::VALUE_ENDPOINT_URL);
        $subscription->setData(self::KEY_FORMAT, self::VALUE_FORMAT);
        $subscription->setData(self::KEY_STATUS, self::VALUE_STATUS);
        $subscription->setData(self::KEY_API_USER_ID, self::VALUE_API_USER_ID);
        $subscription->setData(self::KEY_AUTHENTICATION_TYPE, self::VALUE_AUTHENTICATION_TYPE);
        $subscription->setData(self::KEY_TIMEOUT_IN_SECS, self::VALUE_TIMEOUT_IN_SECS);


        $subscription->save();

        $loadedSubscription = $this->_getSubscription($subscription->getId());

        $this->assertEquals(self::VALUE_NAME, $loadedSubscription->getName());
        $this->assertEquals(self::VALUE_ALIAS, $loadedSubscription->getAlias());
        $this->assertEquals(self::VALUE_ENDPOINT_URL, $loadedSubscription->getEndpointUrl());
        $this->assertEquals(self::VALUE_FORMAT, $loadedSubscription->getFormat());
        $this->assertEquals(self::VALUE_STATUS, $loadedSubscription->getStatus());
        $this->assertEquals(self::VALUE_API_USER_ID, $loadedSubscription->getApiUserId());
        $this->assertEquals(self::VALUE_AUTHENTICATION_TYPE, $loadedSubscription->getAuthenticationType());
        $this->assertEquals(self::VALUE_TIMEOUT_IN_SECS, $loadedSubscription->getTimeoutInSecs());
    }

    public function testGettingData()
    {
        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setName(self::VALUE_NAME);
        $subscription->setAlias(self::VALUE_ALIAS);
        $subscription->setEndpointUrl(self::VALUE_ENDPOINT_URL);
        $subscription->setFormat(self::VALUE_FORMAT);
        $subscription->setStatus(self::VALUE_STATUS);
        $subscription->setApiUserId(self::VALUE_API_USER_ID);
        $subscription->setAuthenticationType(self::VALUE_AUTHENTICATION_TYPE);
        $subscription->setTimeoutInSecs(self::VALUE_TIMEOUT_IN_SECS);

        $subscription->save();

        $loadedSubscription = $this->_getSubscription($subscription->getId());

        $this->assertEquals(self::VALUE_NAME, $loadedSubscription->getData(self::KEY_NAME));
        $this->assertEquals(self::VALUE_ALIAS, $loadedSubscription->getData(self::KEY_ALIAS));
        $this->assertEquals(self::VALUE_ENDPOINT_URL, $loadedSubscription->getData(self::KEY_ENDPOINT_URL));
        $this->assertEquals(self::VALUE_FORMAT, $loadedSubscription->getData(self::KEY_FORMAT));
        $this->assertEquals(self::VALUE_STATUS, $loadedSubscription->getData(self::KEY_STATUS));
        $this->assertEquals(self::VALUE_API_USER_ID, $loadedSubscription->getData(self::KEY_API_USER_ID));
        $this->assertEquals(
            self::VALUE_AUTHENTICATION_TYPE,
            $loadedSubscription->getData(self::KEY_AUTHENTICATION_TYPE)
        );
        $this->assertEquals(self::VALUE_TIMEOUT_IN_SECS, $loadedSubscription->getData(self::KEY_TIMEOUT_IN_SECS));
    }
    
    public function testSetDataArray()
    {
        $data = array(
            self::KEY_NAME                  => self::VALUE_NAME,
            self::KEY_ALIAS                 => self::VALUE_ALIAS,
            self::KEY_ENDPOINT_URL          => self::VALUE_ENDPOINT_URL,
            self::KEY_FORMAT                => self::VALUE_FORMAT,
            self::KEY_STATUS                => self::VALUE_STATUS,
            self::KEY_API_USER_ID           => self::VALUE_API_USER_ID,
            self::KEY_AUTHENTICATION_TYPE   => self::VALUE_AUTHENTICATION_TYPE,
            self::KEY_TIMEOUT_IN_SECS       => self::VALUE_TIMEOUT_IN_SECS,
        );

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setData($data);

        $subscription->save();

        $loadedSubscription = $this->_getSubscription($subscription->getId());

        $this->_assertSubSet($data, $loadedSubscription->getData());
    }

    public function testDeletingEndpoint()
    {
        $data = array(
            self::KEY_NAME                  => self::VALUE_NAME,
            self::KEY_ALIAS                 => self::VALUE_ALIAS,
            self::KEY_ENDPOINT_URL          => self::VALUE_ENDPOINT_URL,
            self::KEY_FORMAT                => self::VALUE_FORMAT,
            self::KEY_STATUS                => self::VALUE_STATUS,
            self::KEY_API_USER_ID           => self::VALUE_API_USER_ID,
            self::KEY_AUTHENTICATION_TYPE   => self::VALUE_AUTHENTICATION_TYPE,
            self::KEY_TIMEOUT_IN_SECS       => self::VALUE_TIMEOUT_IN_SECS,
        );

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setData($data);
        $subscription->save();

        // Load from DB and verify the endpoint is being populated properly
        $subscription = $this->_getSubscription($subscription->getId());
        $endpointId = $subscription->getEndpointId();
        $this->assertNotEquals(0, $endpointId);
        $this->assertNotNull($subscription->getEndpointUrl());

        // Our test, will this delete also trigger the endpoint to be deleted
        $subscription->delete();

        // Create a new subscription, and manually set a link to the old endpoint to see if it still exists.
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setEndpointId($endpointId);

        $this->assertNull($subscription->getEndpointUrl());
    }

    /**
     * Load a subscription from the DB
     *
     * @param $subscriptionId string
     * @return \Magento\Webhook\Model\Subscription
     */
    protected function _getSubscription($subscriptionId)
    {
        $subscription = $this->_objectManager->create('Magento\Webhook\Model\Subscription');
        return $subscription->load($subscriptionId);
    }

    /**
     * Asserts that an array is a subset of another array
     *
     * @param $subSet
     * @param $superSet
     */
    protected function _assertSubSet($subSet, $superSet)
    {
        foreach ($subSet as $key => $value) {
            $this->assertArrayHasKey($key, $superSet);
            $this->assertEquals($value, $superSet[$key]);
        }
    }
    
}
