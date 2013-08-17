<?php
/**
 * Magento_PubSub_Message_DispatcherAsync
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


/**
 * @magentoDbIsolation enabled
 */
class Magento_PubSub_Message_DispatcherAsyncTests extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_PubSub_Message_DispatcherAsync
     */
    protected $_model;

    /**
     * Initialize the model
     */
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

        $this->_model = Mage::getObjectManager()->create('Magento_PubSub_Message_DispatcherAsync');
    }

    /**
     * Test the maing flow of event dispatching
     */
    public function testDispatch()
    {
        $topic = 'webhooks/dispatch/tested';

        $data = array(
            'testKey' => 'testValue'
        );

        $this->_model->dispatch($topic, $data);

        $queue = Mage::getObjectManager()->get('Magento_PubSub_Event_QueueReaderInterface');
        $event = $queue->poll();

        $this->assertEquals($topic, $event->getTopic());
        $this->assertEquals($data, $event->getBodyData());
    }
}