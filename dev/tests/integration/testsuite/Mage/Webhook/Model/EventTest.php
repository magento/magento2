<?php
/**
 * Mage_Webhook_Model_Event
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
class Mage_Webhook_Model_EventTest extends PHPUnit_Framework_TestCase
{
    /** @var  Mage_Webhook_Model_Event */
    private $_event;

    public function setUp()
    {
        $this->_event = Mage::getModel('Mage_Webhook_Model_Event');
    }

    public function testSetGet()
    {
        $this->assertEmpty($this->_event->getBodyData());
        $data = array('body', 'data');
        $this->_event->setBodyData($data);
        $this->assertEquals($data, $this->_event->getBodyData());

        $this->assertEmpty($this->_event->getHeaders());
        $data = array('header', 'array');
        $this->_event->setHeaders($data);
        $this->assertEquals($data, $this->_event->getHeaders());
    }

    public function testSetGetArrays()
    {
        $this->_event->setStatus(42);
        $this->assertEquals(42, $this->_event->getStatus());

        $this->_event->setTopic('customer/topic');
        $this->assertEquals('customer/topic', $this->_event->getTopic());
    }

    public function testMarkAsReadyToSend()
    {
        $this->_event->markAsReadyToSend();
        $this->assertEquals(Magento_PubSub_EventInterface::READY_TO_SEND, $this->_event->getStatus());
    }

    public function testMarkAsProcessed()
    {
        $this->_event->markAsProcessed();
        $this->assertEquals(Magento_PubSub_EventInterface::PROCESSED, $this->_event->getStatus());
    }

    public function testSaveAndLoad()
    {
        $bodyData = array('array', 'of', 'body', 'data');
        $eventId = $this->_event
            ->setBodyData($bodyData)
            ->save()
            ->getId();
        $loadedEvent = Mage::getObjectManager()->create('Mage_Webhook_Model_Event')
            ->load($eventId);
        $this->assertEquals($bodyData, $loadedEvent->getBodyData());
    }
}