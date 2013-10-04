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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Resource\Subscription;

/**
 * \Magento\Webhook\Model\Resource\Subscription\Collection
 *
 * We need DB isolation to avoid confusing interactions with the other Webhook tests.
 *
 * @magentoDbIsolation enabled
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    const TOPIC_LISTENERS_THREE = 'listeners/three';
    const TOPIC_LISTENERS_TWO = 'listeners/two';
    const TOPIC_LISTENERS_ONE = 'listeners/one';
    const TOPIC_UNKNOWN = 'unknown';
    /**
     * API Key for user
     */
    const API_KEY = 'Magento\Webhook\Model\Resource\Subscription\CollectionTest';

    /** @var int */
    private static $_apiUserId;

    /** @var \Magento\Webhook\Model\Resource\Subscription\Collection */
    private $_subscriptionSet;

    /** @var \Magento\Webhook\Model\Subscription[]  */
    private $_subscriptions;

    public static function setUpBeforeClass()
    {
        /** @var \Magento\Webapi\Model\Acl\User $user */
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Webapi\Model\Acl\User');
        $user->loadByKey(self::API_KEY);
        if ($user->getId()) {
            self::$_apiUserId = $user->getId();
        } else {
            /** @var \Magento\Webhook\Model\Webapi\User\Factory $webapiUserFactory */
            $webapiUserFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->create('Magento\Webhook\Model\Webapi\User\Factory');
            self::$_apiUserId = $webapiUserFactory->createUser(
                array(
                    'email'      => 'email@localhost.com',
                    'key'       => self::API_KEY,
                    'secret'    =>'secret'
                ),
                array()
            );
        }
    }

    protected function setUp()
    {
        $this->_subscriptions = array();
        $configModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Config');
        $configModel->setNode('global/webhook/webhooks/listeners/one/label', 'One Listener');
        $configModel->setNode('global/webhook/webhooks/listeners/two/label', 'Two Listeners');
        $configModel->setNode('global/webhook/webhooks/listeners/three/label', 'Three Listeners');

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setAlias('inactive')
            ->setAuthenticationType('hmac')
            ->setEndpointUrl('http://localhost/endpoint')
            ->setFormat('json')
            ->setName('Inactive Subscription')
            ->setTopics(array(self::TOPIC_LISTENERS_THREE))
            ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_INACTIVE)
            ->save();
        $this->_subscriptions[] = $subscription;

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setAlias('first')
            ->setAuthenticationType('hmac')
            ->setEndpointUrl('http://localhost/endpoint')
            ->setFormat('json')
            ->setName('First Subscription')
            ->setTopics(array(self::TOPIC_LISTENERS_THREE))
            ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->save();
        $this->_subscriptions[] = $subscription;

        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setAlias('second')
            ->setAuthenticationType('hmac')
            ->setEndpointUrl('http://localhost/unique_endpoint')
            ->setFormat('json')
            ->setName('Second Subscription')
            ->setTopics(array(self::TOPIC_LISTENERS_TWO, self::TOPIC_LISTENERS_THREE))
            ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->save();
        $this->_subscriptions[] = $subscription;

        $subscription = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Subscription');
        $subscription->setAlias('third')
            ->setAuthenticationType('hmac')
            ->setEndpointUrl('http://localhost/unique_endpoint')
            ->setFormat('json')
            ->setName('Third Subscription')
            ->setTopics(array(self::TOPIC_LISTENERS_ONE, self::TOPIC_LISTENERS_TWO, self::TOPIC_LISTENERS_THREE))
            ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->setApiUserId(self::$_apiUserId)
            ->save();
        $this->_subscriptions[] = $subscription;

        $this->_subscriptionSet = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Resource\Subscription\Collection');
    }

    protected function tearDown()
    {
        foreach ($this->_subscriptions as $subscription) {
            $subscription->delete();
        }
    }

    public function testGetSubscriptions()
    {
        $subscriptions   = $this->_subscriptionSet->getItems();
        $this->assertEquals(4, count($subscriptions));
    }

    public function testGetActiveSubscriptions()
    {
        $subscriptions   = $this->_subscriptionSet->addIsActiveFilter(true)->getItems();
        $this->assertEquals(3, count($subscriptions));
    }

    public function testGetInactiveSubscriptions()
    {
        $subscriptions   = $this->_subscriptionSet->addIsActiveFilter(false)->getItems();
        $this->assertEquals(1, count($subscriptions));
    }

    public function testGetUnknownTopicSubscriptions()
    {
        $subscriptions   = $this->_subscriptionSet->addTopicFilter(self::TOPIC_UNKNOWN)->getItems();
        $this->assertEquals(0, count($subscriptions));
    }

    public function testGetKnownTopicSubscriptions()
    {
        $subscriptions   = $this->_subscriptionSet->addTopicFilter(self::TOPIC_LISTENERS_ONE)->getItems();
        $this->assertEquals(1, count($subscriptions));
    }

    public function testGetSubscriptionsByTopic()
    {
        $subscriptions = $this->_subscriptionSet->getSubscriptionsByTopic(self::TOPIC_LISTENERS_THREE);

        $this->assertEquals(3, count($subscriptions));

        $subscriptions = $this->_subscriptionSet->getSubscriptionsByTopic(self::TOPIC_LISTENERS_TWO);

        $this->assertEquals(2, count($subscriptions));

        $subscriptions = $this->_subscriptionSet->getSubscriptionsByTopic(self::TOPIC_LISTENERS_ONE);

        $this->assertEquals(1, count($subscriptions));
    }

    public function testGetSubscriptionsByAlias()
    {
        $subscriptions = $this->_subscriptionSet->getSubscriptionsByAlias('first');
        // There should only be one item
        foreach ($subscriptions as $subscription) {
            $this->assertEquals('First Subscription', $subscription->getName());
        }
    }

    public function testGetActivatedSubscriptionsWithoutApiUser()
    {
        $subscriptions = $this->_subscriptionSet->getActivatedSubscriptionsWithoutApiUser();

        $this->assertEquals(2, count($subscriptions));
    }

    public function testGetApiUserSubscriptions()
    {
        $subscriptions = $this->_subscriptionSet->getApiUserSubscriptions(self::$_apiUserId);

        $this->assertEquals(1, count($subscriptions));
    }
}
