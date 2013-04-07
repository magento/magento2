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

class Mage_Webhook_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for whole hook sending process
     */
    public function testDispatchEvent()
    {
        // TODO: complete it
        $this->markTestIncomplete("This test hasn't been adopted for refactored code.");

        $this->_prepareService();

        $helper = Mage::helper('Mage_Webhook_Helper_Data');
        $helper->dispatchEvent('test/hook', array('testData' => 'testValue'));

        //TODO: check subscriber

    }

    /**
     * Test for only part which is used by the queue
     */
    public function testSendMessageToSubscriber()
    {
        // TODO: complete it
        $this->markTestIncomplete("This test hasn't been adopted for refactored code.");

        $service = $this->_prepareService();

        $event = Mage::getModel('Mage_Webhook_Model_Event')
            ->setHookName('test/hook')
            ->setStatus(Mage_Webhook_Model_Event::READY_TO_SEND)
            ->save();

        Mage::getModel('Mage_Webhook_Model_Message')
            ->setFormat('json')
            ->setBody(array('testData2' => 'testValue2'))
            ->setHeaders(array('TestHeader2' => 'value2'))
            ->setEventId($event->getId())
            ->save();

        $helper = Mage::helper('Mage_Webhook_Helper_Data');
        $helper->sendMessageToSubscriber($event, $service);

        //TODO: check subscriber

    }

    protected function _prepareService()
    {
        // add new hooks for the tests
        $this->_addHook('test/hook', array(
            'label' => 'Test Hook',
            'message' => 'Test_Hook_Message_Helper_Data',
        ));

        // add new format for tests
        Mage::getConfig()->setNode('global/xcom_messenger/formats/json/label', 'Test Format');
        Mage::getConfig()->setNode('global/xcom_messenger/formats/json/status', 'enabled');

        // save new service which is subscribed to new hook and use new format
        $service = Mage::getModel('Mage_Webhook_Model_Subscriber')
            ->setName('Test tervice')
            ->setEndpointUrl('http://magento2-api.loc/xcom_messenger/endpoint/subscriber?asdf'.
                Mage::helper('Mage_Core_Helper_Data')->uniqHash())
            ->setTopics(array('test/hook'))
            ->setAuthenticationType('hmac')
            ->setTransport('endpoint')
            ->setFormat('json')
            ->setAuthenticationOption('secret', 'qa123123')
            ->save();

        return $service;
    }

    protected function _addHook($name, $options)
    {
        Mage::getConfig()->setNode('global/xcom_messenger/webhooks/'. $name, null);
        foreach ($options as $key => $value) {
            Mage::getConfig()->setNode('global/xcom_messenger/webhooks/'. $name . '/' . $key, $value);
        }
    }
}

class Test_Hook_Message_Helper_Data implements Mage_Webhook_Model_Mapper_Interface
{
    public function __construct()
    {
    }

    public function getData()
    {
        return (object) array('test' => 'value');
    }

    public function getHeaders()
    {
        return array('TestHeader' => 'value');
    }

    public function getTopic()
    {
        return 'test/hook';
    }
}
