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
class Mage_Webhook_Model_Subscriber_CollectionTest extends PHPUnit_Framework_TestCase
{
    private $_subscribers = array();

    public function setUp()
    {
        parent::setUp();
        // create new test subscriber
        $this->_subscribers[] = Mage::getModel('Mage_Webhook_Model_Subscriber')
                ->setName('Test subscriber1')
                ->setEndpointUrl(
            'http://magento2-api.loc/xcom_messenger/endpoint/subscriber?' . Mage::helper('Mage_Core_Helper_Data')
                    ->uniqHash())
                ->setTopics(array('test'))
                ->setAuthenticationType('oauth')
                ->setTransport('endpoint')
                ->setMapping('testMapping')
                ->setStatus(1)
                ->save();

        // create new test subscriber
        $this->_subscribers[] = Mage::getModel('Mage_Webhook_Model_Subscriber')
                ->setName('Test subscriber2')
                ->setEndpointUrl(
            'http://magento2-api.loc/xcom_messenger/endpoint/subscriber?' . Mage::helper('Mage_Core_Helper_Data')
                    ->uniqHash())
                ->setTopics(array('test'))
                ->setAuthenticationType('oauth')
                ->setTransport('endpoint')
                ->setMapping('testMapping')
                ->setStatus(1)
                ->save();

        // create new test subscriber
        $this->_subscribers[] = Mage::getModel('Mage_Webhook_Model_Subscriber')
                ->setName('Test subscriber3')
                ->setEndpointUrl(
            'http://magento2-api.loc/xcom_messenger/endpoint/subscriber?' . Mage::helper('Mage_Core_Helper_Data')
                    ->uniqHash())
                ->setTopics(array('customer/created'))
                ->setAuthenticationType('oauth')
                ->setTransport('endpoint')
                ->setMapping('default')
                ->setStatus(1)
                ->save();

        // create new test subscriber
        $this->_subscribers[] = Mage::getModel('Mage_Webhook_Model_Subscriber')
                ->setName('Test subscriber4')
                ->setEndpointUrl(
            'http://magento2-api.loc/xcom_messenger/endpoint/subscriber?' . Mage::helper('Mage_Core_Helper_Data')
                    ->uniqHash())
                ->setTopics(array('test'))
                ->setAuthenticationType('oauth')
                ->setTransport('endpoint')
                ->setMapping('default')
                ->setStatus(0)
                ->save();
    }

    public function tearDown()
    {
        foreach ($this->_subscribers as $subscriber) {
            $subscriber->delete();
        }
    }

    public function testGetSubscribers()
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriberCollection = Mage::getModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
        $subscribers   = $subscriberCollection->getItems();
        $this->assertEquals(4, count($subscribers));
    }

    public function testGetActiveSubscribers()
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriberCollection = Mage::getModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
        $subscribers   = $subscriberCollection->addIsActiveFilter(true)->getItems();
        $this->assertEquals(3, count($subscribers));
    }

    public function testGetInactiveSubscribers()
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriberCollection = Mage::getModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
        $subscribers   = $subscriberCollection->addIsActiveFilter(false)->getItems();
        $this->assertEquals(1, count($subscribers));
    }

    public function testGetTestTopicSubscribers()
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriberCollection = Mage::getModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
        $subscribers   = $subscriberCollection->addTopicFilter('test')->getItems();
        $this->assertEquals(3, count($subscribers));
    }

    public function testGetCustomerCreatedTopicSubscribers()
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriberCollection = Mage::getModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
        $subscribers   = $subscriberCollection->addTopicFilter('customer/created')->getItems();
        $this->assertEquals(1, count($subscribers));
    }

    public function testGetTestMappingSubscribers()
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriberCollection = Mage::getModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
        $subscribers   = $subscriberCollection->addMappingFilter('testMapping')->getItems();
        $this->assertEquals(2, count($subscribers));
    }

    public function testGetDefaultMappingSubscribers()
    {
        /** @var $subscriberCollection Mage_Webhook_Model_Resource_Subscriber_Collection */
        $subscriberCollection = Mage::getModel('Mage_Webhook_Model_Resource_Subscriber_Collection');
        $subscribers   = $subscriberCollection->addMappingFilter('default')->getItems();
        $this->assertEquals(2, count($subscribers));
    }
}
