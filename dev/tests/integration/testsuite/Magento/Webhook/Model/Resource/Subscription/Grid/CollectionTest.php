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
namespace Magento\Webhook\Model\Resource\Subscription\Grid;

/**
 * \Magento\Webhook\Model\Resource\Subscription\Grid\Collection
 *
 * We need DB isolation to avoid confusing interactions with the other Webhook tests.
 *
 * @magentoDbIsolation enabled
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** Topics */
    const TOPIC_LISTENERS_THREE = 'listeners/three';
    const TOPIC_LISTENERS_TWO = 'listeners/two';
    const TOPIC_LISTENERS_ONE = 'listeners/one';
    const TOPIC_UNKNOWN = 'unknown';

    /**
     * API Key for user
     */
    const API_KEY = 'Magento\Webhook\Model\Resource\Subscription\Grid\CollectionTest';

    /** @var int */
    private static $_apiUserId;

    /** @var \Magento\Webhook\Model\Subscription[]  */
    private $_subscriptions;

    /** @var \Magento\Webhook\Model\Subscription\Config */
    private $_config;

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
        $this->_createSubscriptions();

        $this->_config = $this->_createSubscriptionConfig();
    }

    protected function tearDown()
    {
        foreach ($this->_subscriptions as $subscription) {
            $subscription->delete();
        }
    }

    public function testGetSubscriptions()
    {
        $gridCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Resource\Subscription\Grid\Collection',
                array('subscriptionConfig' => $this->_config));

        $subscriptions   = $gridCollection->getItems();
        $this->assertEquals(5, count($subscriptions));
    }

    /**
     * Create subscription configure
     *
     * @return \Magento\Webhook\Model\Subscription\Config
     */
    protected function _createSubscriptionConfig()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $configMock = $this->getMock('Magento\Webhook\Model\Config', array(), array(), '', false, false);
        $subscriptions = array(
            'subscription_alias' => array(
                'name' => 'Test subscriber',
                'endpoint_url' => 'http://mage.loc/mage-twitter-integration/web/index.php/endpoint',
                'topics' => array(
                    'customer' => array(
                        'created' => '',
                        'updated' => '',
                        'deleted' => '',
                    ),
                    'order' => array(
                        'created'
                    ),
                ),
            ),
        );
        $configMock->expects($this->any())->method('getSubscriptions')->will($this->returnValue($subscriptions));

        return $objectManager->create('Magento\Webhook\Model\Subscription\Config', array(
            'config' => $configMock
        ));
    }

    protected function _createSubscriptions()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_subscriptions = array();

        $configMock = $this->getMock('Magento\Webhook\Model\Config', array(), array(), '', false, false);
        $webHooks = array(
            'listeners' => array(
                'one' => array('label' => 'One Listener'),
                'two' => array('label' => 'Two Listeners'),
                'three' => array('label' => 'Three Listeners'),
            )
        );
        $configMock->expects($this->any())->method('getWebhooks')->will($this->returnValue($webHooks));
        $objectManager->addSharedInstance($configMock, 'Magento\Webhook\Model\Config');

        /** @var \Magento\Webhook\Model\Subscription $subscription */
        $subscription = $objectManager->create('Magento\Webhook\Model\Subscription');
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
        $subscription = $objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setAlias('first')
            ->setAuthenticationType('hmac')
            ->setEndpointUrl('http://localhost/endpoint')
            ->setFormat('json')
            ->setName('First Subscription')
            ->setTopics(array(self::TOPIC_LISTENERS_THREE))
            ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->save();
        $this->_subscriptions[] = $subscription;

        $subscription = $objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setAlias('second')
            ->setAuthenticationType('hmac')
            ->setEndpointUrl('http://localhost/unique_endpoint')
            ->setFormat('json')
            ->setName('Second Subscription')
            ->setTopics(array(self::TOPIC_LISTENERS_TWO, self::TOPIC_LISTENERS_THREE))
            ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->save();
        $this->_subscriptions[] = $subscription;

        $subscription = $objectManager->create('Magento\Webhook\Model\Subscription');
        $subscription->setAlias('third')
            ->setAuthenticationType('hmac')
            ->setEndpointUrl('http://localhost/unique_endpoint')
            ->setFormat('json')
            ->setName('Third Subscription')
            ->setTopics(array(
                self::TOPIC_LISTENERS_ONE,
                self::TOPIC_LISTENERS_TWO,
                self::TOPIC_LISTENERS_THREE))
            ->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE)
            ->setApiUserId(self::$_apiUserId)
            ->save();
        $this->_subscriptions[] = $subscription;
    }
}
