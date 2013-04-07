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
 * @package     Mage_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Webhook_Model_SubscriberTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webhook_Model_Subscriber */
    private $_subscriber = null;

    public function testSetGetHooks()
    {
        $this->_subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber');
        $this->assertEmpty($this->_subscriber->getTopics(), "New subscriber shouldn't be subscribed on any hooks.");

        // add new hooks for the tests
        Mage::getConfig()->setNode('global/webhook/webhooks/test/hook/message/webapi', 'Test_Hook_Message_Subscriber');
        Mage::getConfig()->setNode('global/webhook/webhooks/test/hook/label', 'Test Hook');

        $this->_subscriber->setTopics(array('test/hook', 'test/bbb'));
        // TODO: doesn't allow to add hooks which arent exists in the config
        $this->assertEquals(array('test/hook', 'test/bbb'), $this->_subscriber->getTopics());
        $this->_subscriber->save();

        // check if the hooks are persist
        $loadedSubscriber = $this->_getSubscriber($this->_subscriber->getId());

        $this->assertEquals(array('test/hook'), $loadedSubscriber->getTopics());
    }

    protected function _getSubscriber($subscriberId)
    {
        $subscriber = Mage::getModel('Mage_Webhook_Model_Subscriber');
        return $subscriber->load($subscriberId);
    }

    protected function tearDown()
    {
        $this->_subscriber->delete();
    }
}

class Test_Hook_Message_Subscriber implements Mage_Webhook_Model_Mapper_Interface
{
    public function __construct()
    {
    }

    public function getData()
    {
        return array('test' => 'value');
    }

    public function getHeaders()
    {
        return array('TestHeader' => 'value');
    }

    public function getTopic()
    {
        return 'some/topic';
    }
}
