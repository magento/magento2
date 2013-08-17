<?php
/**
 * Mage_Webhook_Model_Resource_Subscription
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Resource_SubscriptionTest extends PHPUnit_Framework_TestCase
{
    /** @var  Mage_Webhook_Model_Resource_Subscription */
    private $_resource;

    public function setUp()
    {
        $this->_resource = Mage::getObjectManager()->create('Mage_Webhook_Model_Resource_Subscription');
    }

    public function testLoadTopics()
    {
        $topics = array(
            'customer/created',
            'customer/updated',
            'customer/deleted',
        );

        /** @var Mage_Webhook_Model_Subscription $subscription */
        $subscription = Mage::getObjectManager()->create('Mage_Webhook_Model_Subscription');
        $subscription->setTopics($topics);
        $subscription->save();


        $this->_resource->loadTopics($subscription);
        // When topics are not set, getTopics() calls resource's loadTopics method
        $this->assertEquals($topics, $subscription->getTopics());
        $subscription->delete();
    }

    public function testSaveAndLoad()
    {
        $topics = array(
            'customer/created',
            'customer/updated',
            'customer/deleted',
        );

        $subscription = Mage::getObjectManager()->create('Mage_Webhook_Model_Subscription');
        $subscriptionId = $subscription
            ->setTopics($topics)
            ->setName('subscription to load')
            ->save()
            ->getId();

        // This is done so all of the topic save logic is used
        $topics[] = 'order/created';
        unset($topics[0]);
        $topics = array_values($topics); // Fix integer indices
        $subscription->setTopics($topics)
            ->save();

        $loadedSubscription = Mage::getObjectManager()->create('Mage_Webhook_Model_Subscription');
        $loadedSubscription->load($subscriptionId);

        $this->assertEquals('subscription to load', $loadedSubscription->getName());
        $this->assertEquals($topics, $loadedSubscription->getTopics());

    }
}