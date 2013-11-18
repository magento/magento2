<?php
/**
 * \Magento\PubSub\Message\DispatcherAsync
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

namespace Magento\PubSub\Message;

/**
 * @magentoDbIsolation enabled
 */
class DispatcherAsyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PubSub\Message\DispatcherAsync
     */
    protected $_model;

    /**
     * Initialize the model
     */
    protected function setUp()
    {
        /** @var \Magento\Webhook\Model\Resource\Event\Collection $eventCollection */
        $eventCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\Resource\Event\Collection');
        /** @var array $event */
        $events = $eventCollection->getItems();
        /** @var \Magento\Webhook\Model\Event $event */
        foreach ($events as $event) {
            $event->complete();
            $event->save();
        }

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\PubSub\Message\DispatcherAsync');
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

        $queue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\PubSub\Event\QueueReaderInterface');
        $event = $queue->poll();

        $this->assertEquals($topic, $event->getTopic());
        $this->assertEquals($data, $event->getBodyData());
    }
}
